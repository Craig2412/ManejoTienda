<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DirectSaleController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inventory.products.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
    });

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/tables/{table}', [PosController::class, 'show'])->name('tables.show');
        Route::post('/tables/{table}/items', [PosController::class, 'storeItem'])->name('tables.items.store');
        Route::delete('/tables/{table}/items/{orderItem}', [PosController::class, 'destroyItem'])->name('tables.items.destroy');
        Route::post('/tables/{table}/occupy', [PosController::class, 'occupy'])->name('tables.occupy');
        Route::post('/tables/{table}/customer', [PosController::class, 'assignCustomer'])->name('tables.customer.assign');
        Route::post('/tables/{table}/tip', [PosController::class, 'updateTip'])->name('tables.tip.update');
        Route::get('/tables/{table}/checkout', [PosController::class, 'checkout'])->name('tables.checkout');
        Route::post('/tables/{table}/payments', [PosController::class, 'storePayment'])->name('tables.payments.store');
        Route::delete('/tables/{table}/payments/{payment}', [PosController::class, 'destroyPayment'])->name('tables.payments.destroy');
        Route::post('/tables/{table}/close', [PosController::class, 'closeWithPayment'])->name('tables.close');
        Route::post('/tables/{table}/debt', [PosController::class, 'convertToDebt'])->name('tables.debt');
    });

    Route::prefix('direct-sales')->name('direct-sales.')->group(function () {
        Route::get('/', [DirectSaleController::class, 'index'])->name('index');
        Route::get('/paid', [DirectSaleController::class, 'paid'])->name('paid');
        Route::post('/', [DirectSaleController::class, 'create'])->name('create');
        Route::get('/{order}', [DirectSaleController::class, 'show'])->name('show');
        Route::post('/{order}/customer', [DirectSaleController::class, 'assignCustomer'])->name('customer.assign');
        Route::post('/{order}/items', [DirectSaleController::class, 'storeItem'])->name('items.store');
        Route::delete('/{order}/items/{orderItem}', [DirectSaleController::class, 'destroyItem'])->name('items.destroy');
        Route::post('/{order}/tip', [DirectSaleController::class, 'updateTip'])->name('tip.update');
        Route::get('/{order}/checkout', [DirectSaleController::class, 'checkout'])->name('checkout');
        Route::post('/{order}/payments', [DirectSaleController::class, 'storePayment'])->name('payments.store');
        Route::delete('/{order}/payments/{payment}', [DirectSaleController::class, 'destroyPayment'])->name('payments.destroy');
        Route::post('/{order}/close', [DirectSaleController::class, 'close'])->name('close');
        Route::post('/{order}/debt', [DirectSaleController::class, 'convertToDebt'])->name('debt');
        Route::delete('/{order}', [DirectSaleController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    Route::prefix('debts')->name('debts.')->group(function () {
        Route::get('/', [DebtController::class, 'index'])->name('index');
        Route::get('/create', [DebtController::class, 'create'])->name('create');
        Route::post('/', [DebtController::class, 'store'])->name('store');
        Route::get('/{order}', [DebtController::class, 'show'])->name('show');
        Route::put('/{order}/info', [DebtController::class, 'updateInfo'])->name('update-info');
        Route::post('/{order}/items', [DebtController::class, 'storeItem'])->name('items.store');
        Route::delete('/{order}/items/{orderItem}', [DebtController::class, 'destroyItem'])->name('items.destroy');
        Route::get('/{order}/payments', [DebtController::class, 'payments'])->name('payments');
        Route::post('/{order}/payments', [DebtController::class, 'storePayment'])->name('payments.store');
        Route::delete('/{order}/payments/{payment}', [DebtController::class, 'destroyPayment'])->name('payments.destroy');
        Route::post('/{order}/close', [DebtController::class, 'close'])->name('close');
    });

    Route::get('/reports', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/pagos', [ReportController::class, 'payments'])->name('reports.payments');

    Route::middleware('role:admin')->prefix('config')->name('config.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/payment-methods', [ConfigController::class, 'storePaymentMethod'])->name('payment-methods.store');
        Route::patch('/payment-methods/{paymentMethod}', [ConfigController::class, 'togglePaymentMethod'])->name('payment-methods.toggle');
        Route::post('/currencies', [ConfigController::class, 'storeCurrency'])->name('currencies.store');
        Route::patch('/currencies/{currency}', [ConfigController::class, 'toggleCurrency'])->name('currencies.toggle');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });
});
