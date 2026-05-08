<?php

use App\Http\Controllers\GuestEmailVerificationController;
use App\Http\Controllers\Master_Data\Barang\KategoriBarangController;
use App\Http\Controllers\Master_Data\Barang\SatuanBarangController;
use App\Http\Controllers\Master_Data\Customer\CustomerController;
use App\Http\Controllers\Pengaturan\PengaturanSistemController;
use App\Http\Controllers\Pengaturan\PermissionsController;
use App\Http\Controllers\Pengaturan\RolesController;
use App\Http\Controllers\Pengaturan\UserController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('halaman.utama');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/guest/verification', [GuestEmailVerificationController::class, 'index'])
    ->name('guest.verification');
Route::post('/guest/send-verification', [GuestEmailVerificationController::class, 'resend'])
    ->name('kirim.ulang');
Route::post('/send-verification', [GuestEmailVerificationController::class, 'sendVerification'])
    ->name('guest.verify.email.send');
Route::get('/verify-guest-email/{id}', [GuestEmailVerificationController::class, 'verify'])
    ->name('guest.verify.email');

Route::get('/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect()->route('login')->with('status', 'Email Anda berhasil diverifikasi.');
})->middleware('signed')->name('verification.verify');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/change-password', [ProfileController::class, 'change_password'])->name('profile.changepassword');
    Route::get('/profile/{id}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile', [ProfileController::class, 'ganti_password'])->name('ganti.password');
    Route::get('/cetak-kartu/{id}', [ProfileController::class, 'cetak'])->name('cetak.kartu');

    // Pengaturan
    Route::resource('roles', RolesController::class);
    Route::get('edit-roles', [RolesController::class, 'edit']);
    Route::patch('/restore-roles/{id}', [RolesController::class, 'restore']);
    Route::resource('permissions', PermissionsController::class);

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('update');
        Route::get('/trash', [UserController::class, 'trash'])->name('trash');
        Route::put('/restore/{id}', [UserController::class, 'restore_user'])->name('restore');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/verify-user/{id}', [UserController::class, 'verify_user'])->name('verify');
    });

    Route::get('/pengaturan-sistem', [PengaturanSistemController::class, 'index'])->name('pengaturan.sistem');
    Route::get('/pengaturan-sistem/{id}/edit', [PengaturanSistemController::class, 'edit'])->name('pengaturan.edit');
    Route::put('/pengaturan-sistem/{id}/update', [PengaturanSistemController::class, 'store'])->name('pengaturan.update');
    Route::get('/pengaturan-background', [PengaturanSistemController::class, 'login_background_index'])->name('pengaturan.background.index');
    Route::post('/pengaturan-background/store', [PengaturanSistemController::class, 'login_background_store'])->name('pengaturan.background.store');
    Route::get('/pengaturan-background/{id}/edit', [PengaturanSistemController::class, 'login_background_edit'])->name('pengaturan.background.edit');
    Route::delete('/pengaturan-background/{id}', [PengaturanSistemController::class, 'login_background_destroy'])->name('pengaturan.background.delete');

    // Master Data
    Route::resource('customer', CustomerController::class);
    Route::resource('satuan-barang', SatuanBarangController::class);
    Route::resource('kategori-barang', KategoriBarangController::class);




});

require __DIR__.'/auth.php';
