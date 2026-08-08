<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    public function generate(DocumentType $type): string
    {
        return DB::transaction(function () use ($type): string {
            $sequence = DocumentSequence::query()
                ->where('type', $type->value)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = DocumentSequence::query()->create([
                    'type' => $type->value,
                    'last_number' => 0,
                ]);

                $sequence = DocumentSequence::query()
                    ->whereKey($sequence->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $sequence->last_number++;
            $sequence->save();

            return sprintf('%s-%06d', $type->prefix(), $sequence->last_number);
        });
    }
}
