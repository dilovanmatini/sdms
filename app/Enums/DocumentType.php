<?php

namespace App\Enums;

enum DocumentType: string
{
    case Purchase = 'PUR';
    case Invoice = 'INV';
    case Receipt = 'REC';

    public function prefix(): string
    {
        return $this->value;
    }
}
