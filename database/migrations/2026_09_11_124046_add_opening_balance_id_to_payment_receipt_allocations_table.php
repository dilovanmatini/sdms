<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_receipt_allocations', function (Blueprint $table) {
            $table->dropForeign(['sales_invoice_id']);
        });

        Schema::table('payment_receipt_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_invoice_id')->nullable()->change();
            $table->foreignId('opening_balance_id')->nullable()->after('sales_invoice_id')->constrained()->restrictOnDelete();
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_receipt_allocations', function (Blueprint $table) {
            $table->dropForeign(['opening_balance_id']);
            $table->dropColumn('opening_balance_id');
            $table->dropForeign(['sales_invoice_id']);
        });

        Schema::table('payment_receipt_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_invoice_id')->nullable(false)->change();
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->restrictOnDelete();
        });
    }
};
