<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?User $user): Response
    {
        if ($user?->exists) {
            $userPayload = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'is_active' => $user->is_active,
            ];
        } else {
            $userPayload = null;
        }

        return Inertia::render('settings/users/create-edit', [
            'user' => $userPayload,
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return array_map(
            static fn (UserRole $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            UserRole::cases(),
        );
    }
}
