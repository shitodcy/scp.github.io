<?php


use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\TeamController;




Route::get('/', [PageController::class, 'index'])->name('home');





Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    Route::resource('users', UserController::class);
    Route::resource('menu', MenuItemController::class)->parameters([
    'menu' => 'menuItem'
]);
    Route::resource('teams', TeamController::class);

    // Rute untuk Backup Data
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/delete/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');


    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');


});




require __DIR__.'/auth.php';
