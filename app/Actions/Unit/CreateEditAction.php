<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?Unit $unit): Response
    {
        if ($unit?->exists) {
            $unit = [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => $unit->is_active,
            ];
        } else {
            $unit = null;
        }

        return Inertia::render('settings/units/create-edit', [
            'unit' => $unit,
        ]);
    }
}
