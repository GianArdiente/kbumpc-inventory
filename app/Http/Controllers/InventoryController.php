<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\AdjustItemStock;
use App\Actions\Inventory\ArchiveItem;
use App\Actions\Inventory\RestoreItem;
use App\Http\Requests\AdjustItemStockRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Services\StockService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(
        Request $request,
        StockService $stockService
    ): View {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

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
                'in:available,partially_reserved,fully_reserved,low_stock,out_of_stock,overcommitted,archived',
            ],
        ]);

        $items = Item::query()
            ->when(
                filled($validated['search'] ?? null),
                function (Builder $query) use ($validated): void {
                    $search = $validated['search'];

                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'item_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'storage_location',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
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
                    ->where('reserved_quantity', '=', 0)
                    ->whereRaw(
                        'available_quantity > minimum_stock_level'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'partially_reserved',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->where('reserved_quantity', '>', 0)
                    ->whereRaw(
                        'available_quantity - reserved_quantity > minimum_stock_level'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'fully_reserved',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->where('reserved_quantity', '>', 0)
                    ->whereRaw(
                        'available_quantity = reserved_quantity'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'overcommitted',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->whereColumn(
                        'reserved_quantity',
                        '>',
                        'available_quantity'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'low_stock',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->whereRaw(
                        'available_quantity - reserved_quantity > 0'
                    )
                    ->whereRaw(
                        'available_quantity - reserved_quantity <= minimum_stock_level'
                    )
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'out_of_stock',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->where('available_quantity', '<=', 0)
            )
            ->when(
                ($validated['stock_status'] ?? null) === 'archived',
                fn (Builder $query) => $query
                    ->whereNotNull('archived_at')
            )
            ->when(
                ! isset($validated['stock_status'])
                    || $validated['stock_status'] !== 'archived',
                fn (Builder $query) => $query
                    ->whereNull('archived_at')
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $items->getCollection()->transform(
            function (Item $item) use ($stockService): Item {
                $item->stock_snapshot =
                    $stockService->snapshot($item);

                return $item;
            }
        );

        $activeItems = Item::query()
            ->whereNull('archived_at')
            ->get();

        $physicalUnits = 0;
        $reservedUnits = 0;
        $freeUnits = 0;
        $lowStockItems = 0;
        $outOfStockItems = 0;
        $fullyReservedItems = 0;
        $overcommittedItems = 0;

        foreach ($activeItems as $item) {
            $snapshot = $stockService->snapshot($item);

            $physicalUnits += $snapshot['physical'];
            $reservedUnits += $snapshot['reserved'];
            $freeUnits += $snapshot['free'];

            if ($snapshot['status'] === 'low_stock') {
                $lowStockItems++;
            }

            if ($snapshot['status'] === 'out_of_stock') {
                $outOfStockItems++;
            }

            if ($snapshot['status'] === 'fully_reserved') {
                $fullyReservedItems++;
            }

            if ($snapshot['status'] === 'overcommitted') {
                $overcommittedItems++;
            }
        }

        $statistics = [
            'active_items' => $activeItems->count(),
            'physical_units' => $physicalUnits,
            'reserved_units' => $reservedUnits,
            'free_units' => $freeUnits,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'fully_reserved_items' => $fullyReservedItems,
            'overcommitted_items' => $overcommittedItems,
        ];

        return view('inventory.index', [
            'items' => $items,
            'statistics' => $statistics,
            'filters' => $validated,
        ]);
    }

    public function edit(Item $item): View
    {
        abort_unless(
            auth()->user()?->role === 'admin'
                && auth()->user()?->status === 'active',
            403
        );

        abort_if(
            $item->archived_at !== null,
            403,
            'Archived items cannot be edited.'
        );

        return view('inventory.edit', [
            'item' => $item,
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

    public function show(
        Item $item,
        StockService $stockService
    ): View {
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

        $snapshot = $stockService->snapshot($item);

        return view('inventory.show', [
            'item' => $item,
            'stockSnapshot' => $snapshot,
            'stockStatus' => $snapshot['status'],
            'inUseQuantity' => $snapshot['in_use'],
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
            $unitCost = $validated['unit_cost'] ?? null;

            $item = Item::create([
                'item_code' =>
                    $validated['item_code'],

                'name' =>
                    $validated['name'],

                'category' =>
                    $validated['category'],

                'consumption_type' =>
                    $validated['consumption_type'],

                'unit' =>
                    $validated['unit'],

                'unit_cost' =>
                    $unitCost,

                'overall_stock' =>
                    $initialStock,

                'available_quantity' =>
                    $initialStock,

                'reserved_quantity' =>
                    0,

                'minimum_stock_level' =>
                    $validated['minimum_stock_level'],

                'storage_location' =>
                    $validated['storage_location'] ?? null,

                'remarks' =>
                    $validated['remarks'] ?? null,
            ]);

            if ($initialStock > 0) {
                InventoryTransaction::create([
                    'item_id' =>
                        $item->id,

                    'user_id' =>
                        $request->user()->id,

                    'source_type' =>
                        Item::class,

                    'source_id' =>
                        $item->id,

                    'type' =>
                        'opening_stock',

                    'unit_cost_snapshot' =>
                        $unitCost,

                    'total_cost' =>
                        $unitCost !== null
                            ? round(
                                (float) $unitCost * $initialStock,
                                2
                            )
                            : null,

                    'overall_stock_change' =>
                        $initialStock,

                    'available_quantity_change' =>
                        $initialStock,

                    'overall_stock_after' =>
                        $initialStock,

                    'available_quantity_after' =>
                        $initialStock,

                    'remarks' =>
                        'Initial stock recorded during item creation.',
                ]);
            }

            ActivityLog::create([
                'user_id' =>
                    $request->user()->id,

                'action' =>
                    'item_created',

                'subject_type' =>
                    Item::class,

                'subject_id' =>
                    $item->id,

                'description' => sprintf(
                    '%s created inventory item %s.',
                    $request->user()->name,
                    $item->name
                ),

                'properties' => [
                    'item_code' =>
                        $item->item_code,

                    'category' =>
                        $item->category,

                    'consumption_type' =>
                        $item->consumption_type,

                    'unit_cost' =>
                        $item->unit_cost,

                    'initial_stock' =>
                        $initialStock,
                ],
            ]);

            return $item;
        });

        return redirect()
            ->route('inventory.index')
            ->with(
                'status',
                sprintf(
                    '%s: %s (%s) has been added.',
                    ucfirst($item->category),
                    $item->item_code,
                    $item->name
                )
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
                    'adjustment' =>
                        $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('inventory.show', $item)
            ->with(
                'status',
                "Stock for {$item->name} was adjusted successfully."
            );
    }

    public function update(
        UpdateItemRequest $request,
        Item $item
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $request,
            $item
        ): void {
            $before = [
                'item_code' =>
                    $item->item_code,

                'name' =>
                    $item->name,

                'category' =>
                    $item->category,

                'consumption_type' =>
                    $item->consumption_type,

                'unit' =>
                    $item->unit,

                'unit_cost' =>
                    $item->unit_cost,

                'minimum_stock_level' =>
                    $item->minimum_stock_level,

                'storage_location' =>
                    $item->storage_location,

                'remarks' =>
                    $item->remarks,
            ];

            $item->update([
                'item_code' =>
                    $validated['item_code'],

                'name' =>
                    $validated['name'],

                'category' =>
                    $validated['category'],

                'consumption_type' =>
                    $validated['consumption_type'],

                'unit' =>
                    $validated['unit'],

                'unit_cost' =>
                    $validated['unit_cost'] ?? null,

                'minimum_stock_level' =>
                    $validated['minimum_stock_level'],

                'storage_location' =>
                    $validated['storage_location'] ?? null,

                'remarks' =>
                    $validated['remarks'] ?? null,
            ]);

            ActivityLog::create([
                'user_id' =>
                    $request->user()->id,

                'action' =>
                    'item_updated',

                'subject_type' =>
                    Item::class,

                'subject_id' =>
                    $item->id,

                'description' => sprintf(
                    '%s updated inventory item %s.',
                    $request->user()->name,
                    $item->name
                ),

                'properties' => [
                    'before' => $before,

                    'after' => [
                        'item_code' =>
                            $item->item_code,

                        'name' =>
                            $item->name,

                        'category' =>
                            $item->category,

                        'consumption_type' =>
                            $item->consumption_type,

                        'unit' =>
                            $item->unit,

                        'unit_cost' =>
                            $item->unit_cost,

                        'minimum_stock_level' =>
                            $item->minimum_stock_level,

                        'storage_location' =>
                            $item->storage_location,

                        'remarks' =>
                            $item->remarks,
                    ],
                ],
            ]);
        });

        return redirect()
            ->route('inventory.show', $item)
            ->with(
                'status',
                "Inventory item {$item->name} was updated successfully."
            );
    }

    public function archive(
        Item $item,
        ArchiveItem $archiveItem
    ): RedirectResponse {
        try {
            $archiveItem->execute(
                item: $item,
                archivedBy: auth()->user(),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'archive' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('inventory.index')
            ->with(
                'status',
                "Inventory item {$item->name} was archived successfully."
            );
    }

    public function restore(
        Item $item,
        RestoreItem $restoreItem
    ): RedirectResponse {
        try {
            $restoreItem->execute(
                item: $item,
                restoredBy: auth()->user(),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'restore' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('inventory.index', [
                'stock_status' => 'archived',
            ])
            ->with(
                'status',
                "Inventory item {$item->name} was restored successfully."
            );
    }
}