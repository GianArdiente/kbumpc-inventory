<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(
        Request $request
    ): View {
        abort_unless(
            auth()->user()?->role === 'admin'
                && auth()->user()?->status === 'active',
            403
        );
            auth()->user()->forceFill([
                'activity_logs_last_viewed_at' => now(),
            ])->save();

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'action' => [
                'nullable',
                'string',
                'max:100',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $activityLogs = ActivityLog::query()
            ->with([
                'user',
                'subject',
            ])

            /*
             * Search through:
             *
             * - action
             * - description
             * - name of the user who performed the action
             */
            ->when(
                filled($validated['search'] ?? null),
                function (
                    Builder $query
                ) use ($validated): void {
                    $search =
                        trim(
                            $validated['search']
                        );

                    $query->where(
                        function (
                            Builder $query
                        ) use ($search): void {
                            $query
                                ->where(
                                    'action',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                );
                        }
                    );
                }
            )

            /*
             * Exact action filter.
             *
             * Examples:
             *
             * item_created
             * item_updated
             * inventory_adjusted
             * inventory_released
             * item_return_recorded
             * request_mobilized
             */
            ->when(
                filled($validated['action'] ?? null),
                fn (Builder $query) =>
                    $query->where(
                        'action',
                        $validated['action']
                    )
            )

            /*
             * Filter by the user who performed the action.
             */
            ->when(
                filled($validated['user_id'] ?? null),
                fn (Builder $query) =>
                    $query->where(
                        'user_id',
                        $validated['user_id']
                    )
            )

            /*
             * Beginning of selected period.
             */
            ->when(
                filled($validated['date_from'] ?? null),
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '>=',
                        $validated['date_from']
                    )
            )

            /*
             * End of selected period.
             */
            ->when(
                filled($validated['date_to'] ?? null),
                fn (Builder $query) =>
                    $query->whereDate(
                        'created_at',
                        '<=',
                        $validated['date_to']
                    )
            )

            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        /*
         * Populate the action filter using actions that
         * actually exist in the audit log.
         */
        $actions = ActivityLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        /*
         * Only show users who have actually created at
         * least one audit record.
         */
        $users = User::query()
            ->whereHas('activityLogs')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $statistics = [
            'total_logs' =>
                ActivityLog::query()
                    ->count(),

            'today_logs' =>
                ActivityLog::query()
                    ->whereDate(
                        'created_at',
                        today()
                    )
                    ->count(),

            'users_with_activity' =>
                ActivityLog::query()
                    ->whereNotNull('user_id')
                    ->distinct(
                        'user_id'
                    )
                    ->count(
                        'user_id'
                    ),

            'actions_recorded' =>
                ActivityLog::query()
                    ->distinct(
                        'action'
                    )
                    ->count(
                        'action'
                    ),
        ];

        return view(
            'activity-logs.index',
            [
                'activityLogs' =>
                    $activityLogs,

                'actions' =>
                    $actions,

                'users' =>
                    $users,

                'statistics' =>
                    $statistics,

                'filters' =>
                    $validated,
            ]
        );
    }
}