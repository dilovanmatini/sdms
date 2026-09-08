<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?Supplier $supplier): Response
    {
        if ($supplier?->exists) {
            $supplier = [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'notes' => $supplier->notes,
                'is_active' => $supplier->is_active,
            ];
        } else {
            $supplier = null;
        }

        return Inertia::render('suppliers/create-edit', [
            'supplier' => $supplier,
        ]);
    }
}
