<?php

use App\Enums\ReportType;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerStatementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::redirect('users', '/settings/users');

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('create-edit/{category?}', [CategoryController::class, 'createEdit'])->name('categories.create-edit');
        Route::post('{category?}', [CategoryController::class, 'storeUpdate'])->name('categories.store-update');
        Route::delete('{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('distributors', DistributorController::class)->except(['show']);

    Route::post('purchases/{purchase}/post', [PurchaseController::class, 'post'])
        ->name('purchases.post');
    Route::resource('purchases', PurchaseController::class)->except(['show']);

    Route::post('sales-invoices/{sales_invoice}/post', [SalesInvoiceController::class, 'post'])
        ->name('sales-invoices.post');
    Route::resource('sales-invoices', SalesInvoiceController::class)->except(['show']);

    Route::post('payment-receipts/{payment_receipt}/post', [PaymentReceiptController::class, 'post'])
        ->name('payment-receipts.post');
    Route::resource('payment-receipts', PaymentReceiptController::class)->except(['show']);

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

    Route::get('statements', [CustomerStatementController::class, 'index'])->name('statements.index');
    Route::get('statements/print', [CustomerStatementController::class, 'print'])->name('statements.print');
    Route::get('statements/pdf', [CustomerStatementController::class, 'pdf'])->name('statements.pdf');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])
        ->whereIn('report', array_column(ReportType::cases(), 'value'))
        ->name('reports.show');
    Route::get('reports/{report}/print', [ReportController::class, 'print'])
        ->whereIn('report', array_column(ReportType::cases(), 'value'))
        ->name('reports.print');
    Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])
        ->whereIn('report', array_column(ReportType::cases(), 'value'))
        ->name('reports.pdf');
    Route::get('reports/{report}/excel', [ReportController::class, 'excel'])
        ->whereIn('report', array_column(ReportType::cases(), 'value'))
        ->name('reports.excel');
});

require __DIR__.'/settings.php';
