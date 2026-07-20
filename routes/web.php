<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasporController;
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
        Route::post('trash/restore-paspor/{id}', [TrashController::class, 'restorePaspor'])->name('trash.restore-paspor');
        Route::delete('trash/force-paspor/{id}', [TrashController::class, 'forceDeletePaspor'])->name('trash.force-paspor');
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
