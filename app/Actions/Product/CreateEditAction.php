<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?Product $product): Response
    {
        if ($product?->exists) {
            $product->load(['category:id,name', 'unit:id,name,symbol']);

            $payload = [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'name_ar' => $product->name_ar,
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'notes' => $product->notes,
                'is_active' => $product->is_active,
            ];

            return Inertia::render('products/create-edit', [
                'product' => $payload,
                'selected_category' => $product->category
                    ? [
                        'value' => $product->category->id,
                        'label' => $product->category->name,
                    ]
                    : null,
                'selected_unit' => $product->unit
                    ? [
                        'value' => $product->unit->id,
                        'label' => $product->unit->symbol
                            ? "{$product->unit->name} ({$product->unit->symbol})"
                            : $product->unit->name,
                    ]
                    : null,
            ]);
        }

        return Inertia::render('products/create-edit', [
            'product' => null,
            'selected_category' => null,
            'selected_unit' => null,
        ]);
    }
}
