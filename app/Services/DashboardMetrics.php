<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Support\MoneyDisplay;
use App\Support\QuantityDisplay;
use Illuminate\Support\Facades\DB;

class DashboardMetrics
{
    /**
     * @return array{
     *     current_inventory_units: string,
     *     today_sales: string,
     *     month_sales: string,
     *     outstanding_receivables: string,
     *     total_customers: int,
     *     total_products: int,
     *     recent_sales: list<array{id: int, number: string, invoice_date: string|null, distributor: string|null, grand_total: string}>,
     *     recent_payments: list<array{id: int, number: string, receipt_date: string|null, distributor: string|null, total_amount: string}>
     * }
     */
    public function build(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $inventoryUnits = DB::table('inventory_transactions')
            ->selectRaw('COALESCE(SUM(quantity_in), 0) - COALESCE(SUM(quantity_out), 0) as units')
            ->value('units');

        $todaySales = SalesInvoice::query()
            ->where('status', DocumentStatus::Posted)
            ->whereDate('invoice_date', $today)
            ->sum('grand_total');

        $monthSales = SalesInvoice::query()
            ->where('status', DocumentStatus::Posted)
            ->whereDate('invoice_date', '>=', $monthStart)
            ->whereDate('invoice_date', '<=', $monthEnd)
            ->sum('grand_total');

        $outstanding = DB::table('accounts_receivable_entries')
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        $recentSales = SalesInvoice::query()
            ->with('distributor:id,name')
            ->where('status', DocumentStatus::Posted)
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (SalesInvoice $invoice): array => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'distributor' => $invoice->distributor?->name,
                'grand_total' => MoneyDisplay::format($invoice->grand_total, trim: true),
            ])
            ->all();

        $recentPayments = PaymentReceipt::query()
            ->with('distributor:id,name')
            ->withSum('allocations as total_amount', 'amount')
            ->where('status', DocumentStatus::Posted)
            ->latest('receipt_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (PaymentReceipt $receipt): array => [
                'id' => $receipt->id,
                'number' => $receipt->number,
                'receipt_date' => $receipt->receipt_date?->toDateString(),
                'distributor' => $receipt->distributor?->name,
                'total_amount' => MoneyDisplay::format($receipt->total_amount ?? 0, trim: true),
            ])
            ->all();

        return [
            'current_inventory_units' => QuantityDisplay::format($inventoryUnits ?? 0),
            'today_sales' => MoneyDisplay::format($todaySales, trim: true),
            'month_sales' => MoneyDisplay::format($monthSales, trim: true),
            'outstanding_receivables' => MoneyDisplay::format($outstanding ?? 0, trim: true),
            'total_customers' => Distributor::query()->count(),
            'total_products' => Product::query()->count(),
            'recent_sales' => $recentSales,
            'recent_payments' => $recentPayments,
        ];
    }
}
