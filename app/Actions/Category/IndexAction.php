<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexAction
{
    public function handle(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'products_count' => $category->products_count,
                'can_delete' => $category->products_count === 0,
            ]);

        return Inertia::render('categories/index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }
}
