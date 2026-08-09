<?php

namespace App\Http\Controllers;

use App\Actions\InventoryRequests\RecordItemReturn;
use App\Models\InventoryRequest;
use App\Models\RequestItem;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(): View
    {
        $requests = InventoryRequest::query()
            ->whereIn('status', [
                'mobilized',
                'in_use',
            ])
            ->with([
                'encoder',
                'items' => function ($query): void {
                    $query
                        ->where('released_quantity', '>', 0)
                        ->with([
                            'item',
                            'returnRecords',
                        ]);
                },
            ])
            ->latest('mobilized_at')
            ->paginate(15);

        return view('returns.index', [
            'requests' => $requests,
        ]);
    }

    public function create(
        RequestItem $requestItem
    ): View {
        $requestItem->load([
            'item',
            'inventoryRequest',
            'returnRecords',
        ]);

        abort_unless(
            in_array(
                auth()->user()?->role,
                ['admin', 'staff'],
                true
            )
            && auth()->user()?->status === 'active',
            403
        );

        abort_if(
            $requestItem->item->consumption_type
                !== 'non-consumable',
            403,
            'Only non-consumable items may be returned.'
        );

        abort_unless(
            in_array(
                $requestItem->inventoryRequest->status,
                ['mobilized', 'in_use'],
                true
            ),
            403,
            'Returns may only be recorded for mobilized or in-use requests.'
        );

        $accountedQuantity = $requestItem
            ->returnRecords
            ->sum(function ($record): int {
                return
                    $record->good_quantity
                    + $record->damaged_quantity
                    + $record->missing_quantity;
            });

        $remainingQuantity = max(
            0,
            $requestItem->released_quantity
                - $accountedQuantity
        );

        abort_if(
            $remainingQuantity < 1,
            403,
            'This request item has already been fully accounted for.'
        );

        return view('returns.create', [
            'requestItem' => $requestItem,
            'accountedQuantity' => $accountedQuantity,
            'remainingQuantity' => $remainingQuantity,
        ]);
    }

    public function store(
        Request $request,
        RequestItem $requestItem,
        RecordItemReturn $recordItemReturn
    ): RedirectResponse {
        $validated = $request->validate([
            'good_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'damaged_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'missing_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        try {
            $returnRecord = $recordItemReturn->execute(
                requestItem: $requestItem,
                recordedBy: $request->user(),
                goodQuantity: $validated['good_quantity'],
                damagedQuantity: $validated['damaged_quantity'],
                missingQuantity: $validated['missing_quantity'],
                remarks: $validated['remarks'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'return' => $exception->getMessage(),
                ]);
        }

        $returnRecord->load([
            'requestItem.item',
            'requestItem.inventoryRequest',
        ]);

        return redirect()
            ->route('returns.index')
            ->with(
                'status',
                sprintf(
                    'Return for %s on request %s was recorded successfully.',
                    $returnRecord->requestItem->item->name,
                    $returnRecord
                        ->requestItem
                        ->inventoryRequest
                        ->request_number
                )
            );
    }
}