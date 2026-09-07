<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreateEditAction
{
    public function handle(Request $request, ?Category $category)
    {
        if ($category?->exists) {
            $category = [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
            ];
        } else {
            $category = null;
        }

        return Inertia::render('categories/create-edit', [
            'category' => $category,
        ]);
    }
}
