<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;

use App\Http\Controllers\LogistikController;
use App\Http\Controllers\StokBarangController;

use App\Http\Controllers\ProductionDailyController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\GrinderController;
use App\Http\Controllers\MixingController;
use App\Http\Controllers\ProductionOilLogController;
use App\Http\Controllers\DataOvenController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\LabTestController;

use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| GUEST (belum login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Root diarahkan ke login jika belum login
    Route::get('/', fn () => redirect()->route('login'));

    // Login
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'handleLogin'])->name('login.handle');
});

/*
|--------------------------------------------------------------------------
| AUTH (sudah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
   
    // (opsional) jika user mengunjungi root setelah login → arahkan ke dashboard
    Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

    /*
    |----------------------------------------------------------------------
    | ADMIN AREA (khusus admin) — kunci dengan middleware role:admin
    |----------------------------------------------------------------------
    | Pastikan alias middleware 'role' sudah diregistrasi di bootstrap/app.php:
    | $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
    */
    Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {

        // Admin Dashboard (kelola Orders)
        Route::get('/dashboard', [ProductionOrderController::class, 'index'])->name('dashboard');
        Route::post('/orders', [ProductionOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{order}/finish', [ProductionOrderController::class, 'finish'])->name('orders.finish');
        Route::get('/orders/active', [ProductionOrderController::class, 'activeList'])->name('orders.active');

        // Admin Setting - Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');      // daftar + form buat user
        Route::post('/users', [UserController::class, 'store'])->name('users.store');     // create

        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update'); // ubah nama/email/role
        Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.reset'); // reset password

        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy'); // hapus user

         Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
         
    });

    Route::prefix('messages')->as('messages.')->group(function () {
    Route::post('/mark-read',      [MessageController::class, 'markRead'])->name('markRead');        // {id}
    Route::post('/mark-all-read',  [MessageController::class, 'markAllRead'])->name('markAllRead');  // semua
    Route::get('/unread-count',    [MessageController::class, 'unreadCount'])->name('unreadCount');  // jumlah
});
    /*
    |----------------------------------------------------------------------
    | INVENTORY
    |----------------------------------------------------------------------
    */
    Route::prefix('inventory')->as('inventory.')->group(function () {

        // Logistik
        Route::prefix('logistik')->as('logistik.')->controller(LogistikController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
        });

        // Stok Barang
        Route::prefix('stokbarang')->as('stokbarang.')->controller(StokBarangController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });
    });

    /*
    |----------------------------------------------------------------------
    | PRODUCTION
    |----------------------------------------------------------------------
    */
    Route::prefix('production')->as('production.')->group(function () {

        // Data Harian
        Route::get('/daily', [ProductionDailyController::class, 'index'])->name('daily.index');
        Route::post('/daily', [ProductionDailyController::class, 'store'])->name('daily.store');

        // Grinder & Mixing (view ringkas)
        Route::get('/grinder', [GrinderController::class, 'index'])->name('grinder.index');
        Route::get('/mixing',  [MixingController::class, 'index'])->name('mixing.index');

        // Stok Oli
        Route::get('/oil-log',        [ProductionOilLogController::class, 'index'])->name('oil-log.index');
        Route::post('/oil-log',       [ProductionOilLogController::class, 'store'])->name('oil-log.store');
        Route::get('/oil-log/export', [ProductionOilLogController::class, 'export'])->name('oil-log.export'); // <- samakan dgn Blade

        // Data Oven
        Route::get('/data-oven',   [DataOvenController::class, 'index'])->name('oven.index');

        // Data Packing
        Route::get('/data-packing', [PackingController::class, 'index'])->name('packing.index');

        // Uji Lab
        Route::get('/lab',  [LabTestController::class, 'index'])->name('lab.index');
        Route::post('/lab', [LabTestController::class, 'store'])->name('lab.store');
    });
});

/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});
