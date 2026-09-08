<?php

use App\Enums\ReportType;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerStatementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('manifest.json', PwaManifestController::class)->name('pwa.manifest');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::group(['prefix' => 'lookups', 'as' => 'lookups.'], function () {
        Route::get('products', [LookupController::class, 'products'])->name('products');
        Route::get('distributors', [LookupController::class, 'distributors'])->name('distributors');
        Route::get('suppliers', [LookupController::class, 'suppliers'])->name('suppliers');
        Route::get('categories', [LookupController::class, 'categories'])->name('categories');
        Route::get('units', [LookupController::class, 'units'])->name('units');
        Route::get('open-invoices', [LookupController::class, 'openInvoices'])->name('open-invoices');
    });

    Route::redirect('users', '/settings/users');

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('create-edit/{category?}', [CategoryController::class, 'createEdit'])->name('categories.create-edit');
        Route::post('{category?}', [CategoryController::class, 'storeUpdate'])->name('categories.store-update');
        Route::delete('{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('create-edit/{product?}', [ProductController::class, 'createEdit'])->name('products.create-edit');
        Route::post('{product?}', [ProductController::class, 'storeUpdate'])->name('products.store-update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::group(['prefix' => 'suppliers'], function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('create-edit/{supplier?}', [SupplierController::class, 'createEdit'])->name('suppliers.create-edit');
        Route::post('{supplier?}', [SupplierController::class, 'storeUpdate'])->name('suppliers.store-update');
        Route::delete('{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    Route::group(['prefix' => 'distributors'], function () {
        Route::get('/', [DistributorController::class, 'index'])->name('distributors.index');
        Route::get('create-edit/{distributor?}', [DistributorController::class, 'createEdit'])->name('distributors.create-edit');
        Route::post('{distributor?}', [DistributorController::class, 'storeUpdate'])->name('distributors.store-update');
        Route::delete('{distributor}', [DistributorController::class, 'destroy'])->name('distributors.destroy');
    });

    Route::post('purchases/{purchase}/post', [PurchaseController::class, 'post'])
        ->name('purchases.post');
    Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])
        ->name('purchases.cancel');
    Route::group(['prefix' => 'purchases'], function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('create-edit/{purchase?}', [PurchaseController::class, 'createEdit'])->name('purchases.create-edit');
        Route::post('{purchase?}', [PurchaseController::class, 'storeUpdate'])->name('purchases.store-update');
        Route::delete('{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });

    Route::post('sales-invoices/{sales_invoice}/post', [SalesInvoiceController::class, 'post'])
        ->name('sales-invoices.post');
    Route::post('sales-invoices/{sales_invoice}/cancel', [SalesInvoiceController::class, 'cancel'])
        ->name('sales-invoices.cancel');
    Route::get('sales-invoices/{sales_invoice}/print', [SalesInvoiceController::class, 'print'])
        ->name('sales-invoices.print');
    Route::group(['prefix' => 'sales-invoices'], function () {
        Route::get('/', [SalesInvoiceController::class, 'index'])->name('sales-invoices.index');
        Route::get('create-edit/{sales_invoice?}', [SalesInvoiceController::class, 'createEdit'])->name('sales-invoices.create-edit');
        Route::post('{sales_invoice?}', [SalesInvoiceController::class, 'storeUpdate'])->name('sales-invoices.store-update');
        Route::delete('{sales_invoice}', [SalesInvoiceController::class, 'destroy'])->name('sales-invoices.destroy');
    });

    Route::post('payment-receipts/{payment_receipt}/post', [PaymentReceiptController::class, 'post'])
        ->name('payment-receipts.post');
    Route::post('payment-receipts/{payment_receipt}/cancel', [PaymentReceiptController::class, 'cancel'])
        ->name('payment-receipts.cancel');
    Route::get('payment-receipts/{payment_receipt}/print', [PaymentReceiptController::class, 'print'])
        ->name('payment-receipts.print');
    Route::group(['prefix' => 'payment-receipts'], function () {
        Route::get('/', [PaymentReceiptController::class, 'index'])->name('payment-receipts.index');
        Route::get('create-edit/{payment_receipt?}', [PaymentReceiptController::class, 'createEdit'])->name('payment-receipts.create-edit');
        Route::post('{payment_receipt?}', [PaymentReceiptController::class, 'storeUpdate'])->name('payment-receipts.store-update');
        Route::delete('{payment_receipt}', [PaymentReceiptController::class, 'destroy'])->name('payment-receipts.destroy');
    });

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
