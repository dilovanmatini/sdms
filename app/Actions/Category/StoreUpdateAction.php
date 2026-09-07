<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(Request $request, ?Category $category)
    {
        if ($category?->exists) {
            $category->update($request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث الصنف بنجاح.']);

            return to_route('categories.create-edit', $category);
        }

        $category = Category::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء الصنف بنجاح.']);

        return to_route('categories.create-edit', $category);
    }
}
