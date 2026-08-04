<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ApproveInventoryRequest
{
    public function execute(
        InventoryRequest $inventoryRequest,
        User $approvedBy
    ): InventoryRequest {
        return DB::transaction(function () use ($inventoryRequest, $approvedBy) {
            $lockedRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($inventoryRequest->id);

            if ($approvedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot approve requests.'
                );
            }

            if ($approvedBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may approve inventory requests.'
                );
            }

            if ($lockedRequest->status !== 'pending') {
                throw new DomainException(
                    'Only pending requests may be approved.'
                );
            }

            if (! $lockedRequest->items()->exists()) {
                throw new DomainException(
                    'A request must contain at least one item before approval.'
                );
            }

            $oldStatus = $lockedRequest->status;

            $lockedRequest->update([
                'status' => 'approved',
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $approvedBy->id,
                'action' => 'request_approved',
                'subject_type' => InventoryRequest::class,
                'subject_id' => $lockedRequest->id,
                'description' => sprintf(
                    '%s approved request %s.',
                    $approvedBy->name,
                    $lockedRequest->request_number
                ),
                'properties' => [
                    'old_status' => $oldStatus,
                    'new_status' => 'approved',
                ],
            ]);

            return $lockedRequest->fresh();
        });
    }
}