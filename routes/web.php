<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryRouteController;
use App\Http\Controllers\GasTypeController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\SupplierTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('suppliers', SupplierController::class)->except(['show']);

    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store']);
    Route::patch('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::patch('purchase-orders/{purchaseOrder}/invoice', [PurchaseOrderController::class, 'updateInvoice'])->name('purchase-orders.update-invoice');

    Route::get('grn', [GoodsReceivedNoteController::class, 'index'])->name('grn.index');
    Route::get('grn/create', [GoodsReceivedNoteController::class, 'create'])->name('grn.create');
    Route::post('grn', [GoodsReceivedNoteController::class, 'store'])->name('grn.store');
    Route::post('grn/{grn}/approve', [GoodsReceivedNoteController::class, 'approve'])->name('grn.approve');
    Route::get('api/po/{po}/items', [GoodsReceivedNoteController::class, 'getPOItems'])->name('api.po.items');

    Route::resource('supplier-payments', SupplierPaymentController::class)->only(['index', 'store']);
    Route::get('supplier-payments/ledger', [SupplierPaymentController::class, 'ledger'])->name('supplier-payments.ledger');

    Route::resource('customers', CustomerController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

    Route::resource('routes', DeliveryRouteController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('routes/{route}/stops', [DeliveryRouteController::class, 'addStop'])->name('routes.stops.store');
    Route::post('routes/{route}/update-actual', [DeliveryRouteController::class, 'updateActualStart'])->name('routes.update-actual');
    Route::post('route-stops/{stop}/update-time', [DeliveryRouteController::class, 'updateStopTime'])->name('route-stops.update-time');

    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store']);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('supplier-tracking', [SupplierTrackingController::class, 'index'])->name('supplier-tracking.index');
    Route::get('supplier-tracking/{supplier}', [SupplierTrackingController::class, 'show'])->name('supplier-tracking.show');
});
