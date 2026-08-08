<?php

namespace Database\Factories;

use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceiptAllocation>
 */
class PaymentReceiptAllocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_receipt_id' => PaymentReceipt::factory(),
            'sales_invoice_id' => SalesInvoice::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
        ];
    }
}
