<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Category $category): RedirectResponse
    {
        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف گروپ بنجاح.']);

        return to_route('categories.index');
    }
}
