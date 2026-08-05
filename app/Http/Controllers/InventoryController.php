<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Actions\Inventory\AdjustItemStock;
use App\Http\Requests\AdjustItemStockRequest;
use DomainException;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => [
                'nullable',
                'in:tool,equipment,material,consumable',
            ],
            'consumption_type' => [
                'nullable',
                'in:consumable,non-consumable',
            ],
            'stock_status' => [
                'nullable',
                'in:available,low_stock,out_of_stock,archived',
            ],
        ]);

        $items = Item::query()
            ->when(
                filled($validated['search'] ?? null),
                function (Builder $query) use ($validated): void {
                    $search = $validated['search'];

                    $query->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('item_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere(
                                'storage_location',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                filled($validated['category'] ?? null),
                fn (Builder $query) => $query->where(
                    'category',
                    $validated['category']
                )
            )
            ->when(
                filled($validated['consumption_type'] ?? null),
                fn (Builder $query) => $query->where(
                    'consumption_type',
                    $validated['consumption_type']
                )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'available',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->whereColumn(
                        'available_quantity',
                        '>',
                        'minimum_stock_level'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'low_stock',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->where('available_quantity', '>', 0)
                    ->whereColumn(
                        'available_quantity',
                        '<=',
                        'minimum_stock_level'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'out_of_stock',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->where('available_quantity', 0)
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'archived',
                fn (Builder $query) => $query->whereNotNull('archived_at')
            )
            ->when(
                ! isset($validated['stock_status']) ||
                    $validated['stock_status'] !== 'archived',
                fn (Builder $query) => $query->whereNull('archived_at')
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'active_items' => Item::query()
                ->whereNull('archived_at')
                ->count(),

            'available_units' => Item::query()
                ->whereNull('archived_at')
                ->sum('available_quantity'),

            'low_stock_items' => Item::query()
                ->whereNull('archived_at')
                ->where('available_quantity', '>', 0)
                ->whereColumn(
                    'available_quantity',
                    '<=',
                    'minimum_stock_level'
                )
                ->count(),

            'out_of_stock_items' => Item::query()
                ->whereNull('archived_at')
                ->where('available_quantity', 0)
                ->count(),
        ];

        return view('inventory.index', [
            'items' => $items,
            'statistics' => $statistics,
            'filters' => $validated,
        ]);
    }

    public function adjust(Item $item): View
    {
        abort_unless(
            auth()->user()?->role === 'admin'
                && auth()->user()?->status === 'active',
            403
        );

        abort_if(
            $item->archived_at !== null,
            403,
            'Archived items cannot be adjusted.'
        );

        return view('inventory.adjust', [
            'item' => $item,
        ]);
    }

    public function create(): View
    {
        abort_unless(
            auth()->user()?->role === 'admin'
                && auth()->user()?->status === 'active',
            403
        );

        return view('inventory.create');
    }

    public function show(Item $item): View
    {
        $item->load([
            'inventoryTransactions' => fn ($query) => $query
                ->with('user')
                ->latest()
                ->limit(20),
            'activityLogs' => fn ($query) => $query
                ->with('user')
                ->latest()
                ->limit(20),
            'requestItems.inventoryRequest',
        ]);

        $stockStatus = match (true) {
            $item->archived_at !== null => 'archived',
            $item->available_quantity === 0 => 'out_of_stock',
            $item->available_quantity <= $item->minimum_stock_level
                => 'low_stock',
            default => 'available',
        };

        $inUseQuantity = max(
            0,
            $item->overall_stock - $item->available_quantity
        );

        return view('inventory.show', [
            'item' => $item,
            'stockStatus' => $stockStatus,
            'inUseQuantity' => $inUseQuantity,
        ]);
    }

    public function store(
        StoreItemRequest $request
    ): RedirectResponse {
        $validated = $request->validated();
        $initialStock = $validated['initial_stock'];

        $item = DB::transaction(function () use (
            $validated,
            $initialStock,
            $request
        ): Item {
            $item = Item::create([
                'item_code' => $validated['item_code'],
                'name' => $validated['name'],
                'category' => $validated['category'],
                'consumption_type' =>
                    $validated['consumption_type'],
                'unit' => $validated['unit'],
                'overall_stock' => $initialStock,
                'available_quantity' => $initialStock,
                'minimum_stock_level' =>
                    $validated['minimum_stock_level'],
                'storage_location' =>
                    $validated['storage_location'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($initialStock > 0) {
                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => $request->user()->id,
                    'source_type' => Item::class,
                    'source_id' => $item->id,
                    'type' => 'opening_stock',
                    'overall_stock_change' => $initialStock,
                    'available_quantity_change' => $initialStock,
                    'overall_stock_after' => $initialStock,
                    'available_quantity_after' => $initialStock,
                    'remarks' => 'Initial stock recorded during item creation.',
                ]);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'item_created',
                'subject_type' => Item::class,
                'subject_id' => $item->id,
                'description' => sprintf(
                    '%s created inventory item %s.',
                    $request->user()->name,
                    $item->name
                ),
                'properties' => [
                    'item_code' => $item->item_code,
                    'category' => $item->category,
                    'consumption_type' =>
                        $item->consumption_type,
                    'initial_stock' => $initialStock,
                ],
            ]);

            return $item;
        });

        return redirect()
            ->route('inventory.index')
            ->with(
                'status',
                "Inventory item {$item->name} was added successfully."
            );
    }

    public function updateStock(
        AdjustItemStockRequest $request,
        Item $item,
        AdjustItemStock $adjustItemStock
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $adjustItemStock->execute(
                item: $item,
                adjustedBy: $request->user(),
                overallChange: $validated['overall_change'],
                availableChange: $validated['available_change'],
                type: $validated['type'],
                remarks: $validated['remarks'],
            );
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'adjustment' => $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('inventory.show', $item)
            ->with(
                'status',
                "Stock for {$item->name} was adjusted successfully."
            );
    }
}