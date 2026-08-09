<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(
        Request $request
    ): View {
        $this->ensureAdmin();

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'role' => [
                'nullable',
                Rule::in([
                    'admin',
                    'staff',
                ]),
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        $users = User::query()
            ->when(
                filled($validated['search'] ?? null),
                function ($query) use ($validated): void {
                    $search =
                        trim(
                            $validated['search']
                        );

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                filled($validated['role'] ?? null),
                fn ($query) =>
                    $query->where(
                        'role',
                        $validated['role']
                    )
            )
            ->when(
                filled($validated['status'] ?? null),
                fn ($query) =>
                    $query->where(
                        'status',
                        $validated['status']
                    )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total_users' =>
                User::query()
                    ->count(),

            'active_users' =>
                User::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->count(),

            'inactive_users' =>
                User::query()
                    ->where(
                        'status',
                        'inactive'
                    )
                    ->count(),

            'admin_users' =>
                User::query()
                    ->where(
                        'role',
                        'admin'
                    )
                    ->count(),

            'staff_users' =>
                User::query()
                    ->where(
                        'role',
                        'staff'
                    )
                    ->count(),
        ];

        return view(
            'users.index',
            [
                'users' =>
                    $users,

                'statistics' =>
                    $statistics,

                'filters' =>
                    $validated,
            ]
        );
    }

    public function create(): View
    {
        $this->ensureAdmin();

        return view(
            'users.create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'staff',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        $user = DB::transaction(
            function () use (
                $validated
            ): User {
                $user = User::create([
                    'name' =>
                        $validated['name'],

                    'email' =>
                        $validated['email'],

                    'password' =>
                        Hash::make(
                            $validated['password']
                        ),

                    'role' =>
                        $validated['role'],

                    'status' =>
                        $validated['status'],
                ]);

                ActivityLog::create([
                    'user_id' =>
                        auth()->id(),

                    'action' =>
                        'user_created',

                    'subject_type' =>
                        User::class,

                    'subject_id' =>
                        $user->id,

                    'description' =>
                        sprintf(
                            '%s created user account %s.',
                            auth()->user()->name,
                            $user->name
                        ),

                    'properties' => [
                        'name' =>
                            $user->name,

                        'email' =>
                            $user->email,

                        'role' =>
                            $user->role,

                        'status' =>
                            $user->status,
                    ],
                ]);

                return $user;
            }
        );

        return redirect()
            ->route(
                'users.index'
            )
            ->with(
                'success',
                "User {$user->name} was created successfully."
            );
    }

    public function edit(
        User $user
    ): View {
        $this->ensureAdmin();

        return view(
            'users.edit',
            [
                'user' =>
                    $user,
            ]
        );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore(
                    $user->id
                ),
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'staff',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        DB::transaction(
            function () use (
                $user,
                $validated
            ): void {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $user->id
                    );

                if (
                    $lockedUser->id
                    === auth()->id()
                    && $validated['status']
                    === 'inactive'
                ) {
                    throw new DomainException(
                        'You cannot deactivate your own account.'
                    );
                }

                if (
                    $lockedUser->id
                    === auth()->id()
                    && $validated['role']
                    !== 'admin'
                ) {
                    throw new DomainException(
                        'You cannot remove your own Admin role.'
                    );
                }

                if (
                    $lockedUser->role
                    === 'admin'
                    && (
                        $validated['role']
                        !== 'admin'
                        || $validated['status']
                        !== 'active'
                    )
                ) {
                    $remainingActiveAdmins =
                        User::query()
                            ->where(
                                'role',
                                'admin'
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->whereKeyNot(
                                $lockedUser->id
                            )
                            ->count();

                    if (
                        $remainingActiveAdmins < 1
                    ) {
                        throw new DomainException(
                            'At least one active Admin account must remain.'
                        );
                    }
                }

                $before = [
                    'name' =>
                        $lockedUser->name,

                    'email' =>
                        $lockedUser->email,

                    'role' =>
                        $lockedUser->role,

                    'status' =>
                        $lockedUser->status,
                ];

                $updateData = [
                    'name' =>
                        $validated['name'],

                    'email' =>
                        $validated['email'],

                    'role' =>
                        $validated['role'],

                    'status' =>
                        $validated['status'],
                ];

                if (
                    filled(
                        $validated['password']
                        ?? null
                    )
                ) {
                    $updateData['password'] =
                        Hash::make(
                            $validated['password']
                        );
                }

                $lockedUser->update(
                    $updateData
                );

                ActivityLog::create([
                    'user_id' =>
                        auth()->id(),

                    'action' =>
                        'user_updated',

                    'subject_type' =>
                        User::class,

                    'subject_id' =>
                        $lockedUser->id,

                    'description' =>
                        sprintf(
                            '%s updated user account %s.',
                            auth()->user()->name,
                            $lockedUser->name
                        ),

                    'properties' => [
                        'before' =>
                            $before,

                        'after' => [
                            'name' =>
                                $lockedUser->name,

                            'email' =>
                                $lockedUser->email,

                            'role' =>
                                $lockedUser->role,

                            'status' =>
                                $lockedUser->status,
                        ],

                        'password_changed' =>
                            filled(
                                $validated['password']
                                ?? null
                            ),
                    ],
                ]);
            }
        );

        return redirect()
            ->route(
                'users.index'
            )
            ->with(
                'success',
                'User account updated successfully.'
            );
    }

    private function ensureAdmin(): void
    {
        abort_unless(
            auth()->check()
                && auth()->user()->role === 'admin'
                && auth()->user()->status === 'active',
            403
        );
    }
}