<?php

use App\Http\Controllers\Settings\BackupController;
use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\UnitController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');

    Route::get('settings/general', [GeneralSettingsController::class, 'edit'])->name('settings.general.edit');
    Route::put('settings/general', [GeneralSettingsController::class, 'update'])->name('settings.general.update');

    Route::group(['prefix' => 'settings/backups'], function () {
        Route::get('/', [BackupController::class, 'index'])->name('settings.backups.index');
        Route::post('/', [BackupController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('settings.backups.store');
        Route::get('{backup}/download', [BackupController::class, 'download'])->name('settings.backups.download');
        Route::delete('{backup}', [BackupController::class, 'destroy'])->name('settings.backups.destroy');
    });

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::group(['prefix' => 'settings/units'], function () {
        Route::get('/', [UnitController::class, 'index'])->name('units.index');
        Route::get('create-edit/{unit?}', [UnitController::class, 'createEdit'])->name('units.create-edit');
        Route::post('{unit?}', [UnitController::class, 'storeUpdate'])->name('units.store-update');
        Route::delete('{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    Route::group(['prefix' => 'settings/users'], function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('create-edit/{user?}', [UserController::class, 'createEdit'])->name('users.create-edit');
        Route::post('{user?}', [UserController::class, 'storeUpdate'])->name('users.store-update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
Route::middleware(['auth'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
