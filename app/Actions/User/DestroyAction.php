<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(User $user): RedirectResponse
    {
        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المستخدم بنجاح.']);

        return to_route('users.index');
    }
}
