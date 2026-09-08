<?php

namespace App\Actions\Distributor;

use App\Models\Distributor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?Distributor $distributor): Response
    {
        if ($distributor?->exists) {
            $distributor = [
                'id' => $distributor->id,
                'name' => $distributor->name,
                'contact_person' => $distributor->contact_person,
                'phone' => $distributor->phone,
                'address' => $distributor->address,
                'credit_limit' => $distributor->credit_limit,
                'notes' => $distributor->notes,
                'is_active' => $distributor->is_active,
            ];
        } else {
            $distributor = null;
        }

        return Inertia::render('distributors/create-edit', [
            'distributor' => $distributor,
        ]);
    }
}
