<?php

// Import semua controller yang dibutuhkan
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController; // Controller untuk halaman publik
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\TeamController;

/*
|--------------------------------------------------------------------------
| Rute Halaman Publik
|--------------------------------------------------------------------------
*/

// Rute ini akan menangani halaman utama (landing page)
// dan mengirimkan data menu sekaligus data tim ke view 'index'
Route::get('/', [PageController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| Rute Admin
|--------------------------------------------------------------------------
*/

// Grup untuk semua rute admin
// Hanya bisa diakses oleh user yang sudah login dan terverifikasi
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute untuk CRUD (Create, Read, Update, Delete)
    // Route::resource akan otomatis membuat semua rute yang dibutuhkan
    Route::resource('users', UserController::class);
    Route::resource('menu', MenuItemController::class)->parameters([
    'menu' => 'menuItem'
]);
    Route::resource('teams', TeamController::class); // Rute tim Anda sudah benar di sini

    // Rute untuk Backup Data
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/delete/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

    // Rute untuk Log Aktivitas
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');
    

});


/*
|--------------------------------------------------------------------------
| Rute Autentikasi
|--------------------------------------------------------------------------
*/
// Rute ini dibuat oleh Laravel Breeze/UI untuk login, register, dll.
require __DIR__.'/auth.php';