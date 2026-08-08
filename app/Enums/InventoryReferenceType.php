<?php

namespace App\Enums;

enum InventoryReferenceType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
}
