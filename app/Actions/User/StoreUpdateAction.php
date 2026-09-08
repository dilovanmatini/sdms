<?php

namespace App\Actions\User;

use App\Http\Requests\StoreUpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(StoreUpdateUserRequest $request, ?User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['username'] = Str::lower($data['username']);

        if ($user?->exists) {
            if (filled($data['password'] ?? null)) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المستخدم بنجاح.']);

            return to_route('users.create-edit', $user);
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المستخدم بنجاح.']);

        return to_route('users.create-edit', $user);
    }
}
