<?php

namespace App\Actions\User;

use App\Actions\Concerns\FiltersByActiveStatus;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersByActiveStatus;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $isActive = $this->activeStatusFilter($request);

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->tap(fn ($query) => $this->applyActiveStatusFilter($query, $isActive))
            ->latest('id')
            ->paginate($this->perPage($request, 'users'))
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'is_active' => $user->is_active,
            ]);

        return Inertia::render('settings/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'is_active' => $isActive,
            ],
            'active_status_options' => $this->activeStatusOptions(),
        ]);
    }
}
