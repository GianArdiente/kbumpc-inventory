<?php

namespace App\Http\Controllers;

use App\Actions\InventoryRequests\AddRequestItem;
use App\Actions\InventoryRequests\ApproveInventoryRequest;
use App\Http\Requests\StoreInventoryRequestRequest;
use App\Http\Requests\StoreRequestItemRequest;
use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Actions\InventoryRequests\MobilizeInventoryRequest;
use App\Models\Item;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search')
        );

        $status = $request->input('status');

        $requests = InventoryRequest::query()
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'request_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'foreman_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'work_area',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                filled($status),
                fn ($query) => $query->where(
                    'status',
                    $status
                )
            )
            ->latest('requested_at')
            ->paginate(15)
            ->withQueryString();

        return view('requests.index', [
            'requests' => $requests,
            'search' => $search,
            'status' => $status,

            'requestCounts' => [
                'all' => InventoryRequest::query()->count(),

                'pending' => InventoryRequest::query()
                    ->where('status', 'pending')
                    ->count(),

                'approved' => InventoryRequest::query()
                    ->where('status', 'approved')
                    ->count(),

                'mobilized' => InventoryRequest::query()
                    ->where('status', 'mobilized')
                    ->count(),

                'in_use' => InventoryRequest::query()
                    ->where('status', 'in_use')
                    ->count(),

                'completed' => InventoryRequest::query()
                    ->where('status', 'completed')
                    ->count(),
            ],
        ]);
    }

    public function show(
        InventoryRequest $inventoryRequest
    ): View {
        $inventoryRequest->load([
            'encoder',
            'approver',
            'mobilizer',
            'items.item',
            'items.returnRecords',

            'activityLogs' => fn ($query) => $query
                ->with('user')
                ->latest(),
        ]);

        /*
         * Only pending requests need the item-selection list.
         * Items with no unreserved stock are excluded.
         */
        $activeItems = collect();

        if ($inventoryRequest->status === 'pending') {
            $activeItems = Item::query()
                ->whereNull('archived_at')
                ->whereRaw(
                    'available_quantity - reserved_quantity > 0'
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'item_code',
                    'name',
                    'category',
                    'consumption_type',
                    'unit',
                    'available_quantity',
                    'reserved_quantity',
                ]);
        }

        return view('requests.show', [
            'inventoryRequest' => $inventoryRequest,
            'activeItems' => $activeItems,
        ]);
    }

    public function create(): View
    {
        return view('requests.create');
    }

    public function store(
        StoreInventoryRequestRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        return DB::transaction(function () use (
            $request,
            $validated
        ): RedirectResponse {
            $latestRequest = InventoryRequest::query()
                ->latest('id')
                ->first();

            $nextNumber = $latestRequest
                ? ((int) substr(
                    $latestRequest->request_number,
                    -4
                )) + 1
                : 1;

            $inventoryRequest = InventoryRequest::create([
                'request_number' => sprintf(
                    'REQ-%s-%04d',
                    now()->year,
                    $nextNumber
                ),

                'foreman_name' =>
                    $validated['foreman_name'],

                'project_name' =>
                    $validated['project_name'],

                'work_area' =>
                    $validated['work_area'],

                'requested_at' =>
                    $validated['requested_at'],

                'encoded_by' =>
                    $request->user()->id,

                'remarks' =>
                    $validated['remarks'] ?? null,

                'status' => 'pending',
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'request_created',
                'subject_type' => InventoryRequest::class,
                'subject_id' => $inventoryRequest->id,

                'description' => sprintf(
                    '%s created request %s.',
                    $request->user()->name,
                    $inventoryRequest->request_number
                ),

                'properties' => [
                    'foreman' =>
                        $inventoryRequest->foreman_name,

                    'project' =>
                        $inventoryRequest->project_name,

                    'work_area' =>
                        $inventoryRequest->work_area,
                ],
            ]);

            return redirect()
                ->route(
                    'requests.show',
                    $inventoryRequest
                )
                ->with(
                    'status',
                    "Request {$inventoryRequest->request_number} created successfully."
                );
        });
    }

    public function storeItem(
        StoreRequestItemRequest $request,
        InventoryRequest $inventoryRequest,
        AddRequestItem $addRequestItem
    ): RedirectResponse {
        $validated = $request->validated();

        $item = Item::query()->findOrFail(
            $validated['item_id']
        );

        try {
            $requestItem = $addRequestItem->execute(
                inventoryRequest: $inventoryRequest,
                item: $item,
                addedBy: $request->user(),
                quantity: $validated['requested_quantity'],
                remarks: $validated['remarks'] ?? null,
            );

            $requestItem->load('item');
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'request_item' =>
                        $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route(
                'requests.show',
                $inventoryRequest
            )
            ->with(
                'status',
                sprintf(
                    '%s: %s (%s) now has a requested quantity of %s.',
                    ucfirst($requestItem->item->category),
                    $requestItem->item->item_code,
                    $requestItem->item->name,
                    number_format(
                        $requestItem->requested_quantity
                    )
                )
            );
    }

    public function approve(
        InventoryRequest $inventoryRequest,
        ApproveInventoryRequest $approveInventoryRequest
    ): RedirectResponse {
        try {
            $approveInventoryRequest->execute(
                inventoryRequest: $inventoryRequest,
                approvedBy: auth()->user(),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'request_action' =>
                    $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route(
                'requests.show',
                $inventoryRequest
            )
            ->with(
                'status',
                "Request {$inventoryRequest->request_number} was approved and its inventory was reserved."
            );
    }

    public function mobilize(
        InventoryRequest $inventoryRequest,
        MobilizeInventoryRequest $mobilizeInventoryRequest
    ): RedirectResponse {   
        try {
            $mobilizeInventoryRequest->execute(
                inventoryRequest: $inventoryRequest,
                mobilizedBy: auth()->user(),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'request_action' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('requests.show', $inventoryRequest)
            ->with(
                'status',
                "Request {$inventoryRequest->request_number} was mobilized successfully."
            );
    }
}