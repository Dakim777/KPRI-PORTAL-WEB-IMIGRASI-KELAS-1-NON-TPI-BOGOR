<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\KategoriKantinController;
use App\Http\Controllers\PasporController;
use App\Http\Controllers\ProdukKantinController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Redirect root to login ────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── Authentication ────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Authenticated routes ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard — role-aware redirect
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::post('/notifications/{id}/read', function ($id) {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
        return back();
    })->name('notifications.read');

    // ── Super Admin Dashboard ───────────────────────────────────────────────
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'superAdmin'])->name('dashboard');

        // ── Kelola Akun (CRUD Data Pengguna Admin/Kasir) ────────────────────
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        
        // Audit Trail
        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs');

        // Soft Deletes / Trash
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::post('trash/restore-produk/{id}', [TrashController::class, 'restoreProduk'])->name('trash.restore-produk');
        Route::delete('trash/force-produk/{id}', [TrashController::class, 'forceDeleteProduk'])->name('trash.force-produk');
        Route::post('trash/restore-paspor/{id}', [TrashController::class, 'restorePaspor'])->name('trash.restore-paspor');
        Route::delete('trash/force-paspor/{id}', [TrashController::class, 'forceDeletePaspor'])->name('trash.force-paspor');
        Route::post('trash/restore-transaksi-kantin/{id}', [TrashController::class, 'restoreTransaksiKantin'])->name('trash.restore-transaksi-kantin');
        Route::delete('trash/force-transaksi-kantin/{id}', [TrashController::class, 'forceDeleteTransaksiKantin'])->name('trash.force-transaksi-kantin');
    });

    // ── Modul Kantin (kasir_kantin + super_admin) ───────────────────────────
    Route::middleware('role:kasir_kantin,super_admin')->prefix('kantin')->name('kantin.')->group(function () {

        // Dashboard Kantin
        Route::get('/dashboard', [DashboardController::class, 'kantin'])->name('dashboard');

        // Pembelian / Barang Masuk
        Route::get('/pembelian/search-barcode', [\App\Http\Controllers\PembelianController::class, 'searchBarcode'])->name('pembelian.search-barcode');
        Route::resource('pembelian', \App\Http\Controllers\PembelianController::class)->only(['index', 'create', 'store']);

        // Master Produk (CRUD + Import Excel)
        Route::post('/produk/import', [ProdukKantinController::class, 'importExcel'])->name('produk.import');
        Route::get('/produk/template', [ProdukKantinController::class, 'downloadTemplate'])->name('produk.template');
        Route::resource('produk', ProdukKantinController::class);

        // Kategori Produk (CRUD)
        Route::resource('kategori', KategoriKantinController::class)->except(['show']);

        // POS Transaksi
        Route::get('/pos', [KantinController::class, 'pos'])->name('pos');
        Route::post('/pos', [KantinController::class, 'simpanTransaksi'])->name('pos.simpan');
        Route::get('/transaksi', [KantinController::class, 'riwayat'])->name('transaksi');
        Route::get('/transaksi/{transaksi}/print', [KantinController::class, 'printReceipt'])->name('transaksi.print');
        Route::get('/transaksi/{transaksi}', [KantinController::class, 'showTransaksi'])->name('transaksi.show');
        Route::delete('/transaksi/{transaksi}', [KantinController::class, 'destroyTransaksi'])->name('transaksi.destroy');

        // Laporan penjualan & pembelian — export Excel
        Route::get('/laporan/export', [KantinController::class, 'exportExcel'])->name('laporan.export');
        Route::get('/laporan/pembelian/export', [KantinController::class, 'exportPembelianExcel'])->name('laporan.pembelian.export');
    });

    // ── Modul Paspor (kasir_paspor + super_admin) ───────────────────────────
    Route::middleware('role:kasir_paspor,super_admin')->prefix('paspor')->name('paspor.')->group(function () {

        // Dashboard Paspor
        Route::get('/dashboard', [DashboardController::class, 'paspor'])->name('dashboard');

        Route::get('/', [PasporController::class, 'index'])->name('index');
        Route::get('/create', [PasporController::class, 'create'])->name('create');
        Route::post('/', [PasporController::class, 'store'])->name('store');
        Route::get('/{paspor}/edit', [PasporController::class, 'edit'])->name('edit');
        Route::put('/{paspor}', [PasporController::class, 'update'])->name('update');
        Route::get('/export/kompleks', [PasporController::class, 'exportKompleks'])->name('export.kompleks');
        Route::get('/{paspor}/print', [PasporController::class, 'printReceipt'])->name('print');
        Route::patch('/{paspor}/status', [PasporController::class, 'updateStatus'])->name('update-status');
        Route::get('/{paspor}', [PasporController::class, 'show'])->name('show');
        Route::delete('/{paspor}', [PasporController::class, 'destroy'])->name('destroy');
    });
});
