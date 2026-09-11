<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->default(0)->after('payment_method');
        });

        $receipts = DB::table('payment_receipts')->pluck('id');

        foreach ($receipts as $receiptId) {
            $sum = DB::table('payment_receipt_allocations')
                ->where('payment_receipt_id', $receiptId)
                ->sum('amount');

            DB::table('payment_receipts')
                ->where('id', $receiptId)
                ->update(['amount' => $sum]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
