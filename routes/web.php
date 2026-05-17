<?php

use App\Http\Controllers\Auth\IdleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\General\CashBankController;
use App\Http\Controllers\General\CompanyInfoController;
use App\Http\Controllers\General\CurrencyController;
use App\Http\Controllers\General\GeneralSettingController;
use App\Http\Controllers\GuestEmailVerificationController;
use App\Http\Controllers\Master_Data\Barang\DataBarangController;
use App\Http\Controllers\Master_Data\Barang\KategoriBarangController;
use App\Http\Controllers\Master_Data\Barang\SatuanBarangController;
use App\Http\Controllers\Master_Data\CustomerController;
use App\Http\Controllers\Master_Data\DaftarKendaraanController;
use App\Http\Controllers\Master_Data\SalesmanController;
use App\Http\Controllers\Master_Data\SupplierController;
use App\Http\Controllers\Master_Data\WarehouseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pengaturan\PengaturanSistemController;
use App\Http\Controllers\Pengaturan\PermissionsController;
use App\Http\Controllers\Pengaturan\RolesController;
use App\Http\Controllers\Pengaturan\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Transaction\PurchaseOrderController;
use App\Http\Controllers\Transaction\PurchaseRequisitionController;
use App\Http\Controllers\Transaction\SalesOrderController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('halaman.utama');

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
    Route::group(['middleware' => ['role:Super Admin']], function () {

        Route::get('/pengaturan-sistem', [PengaturanSistemController::class, 'index'])->name('pengaturan.sistem');
        Route::get('/pengaturan-sistem/{id}/edit', [PengaturanSistemController::class, 'edit'])->name('pengaturan.edit');
        Route::put('/pengaturan-sistem/{id}/update', [PengaturanSistemController::class, 'store'])->name('pengaturan.update');
        Route::get('/pengaturan-background', [PengaturanSistemController::class, 'login_background_index'])->name('pengaturan.background.index');
        Route::post('/pengaturan-background/store', [PengaturanSistemController::class, 'login_background_store'])->name('pengaturan.background.store');
        Route::get('/pengaturan-background/{id}/edit', [PengaturanSistemController::class, 'login_background_edit'])->name('pengaturan.background.edit');
        Route::delete('/pengaturan-background/{id}', [PengaturanSistemController::class, 'login_background_destroy'])->name('pengaturan.background.delete');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/company-info', [CompanyInfoController::class, 'index'])->name('company.info');
    Route::get('/company-info/{id}/edit', [CompanyInfoController::class, 'edit'])->name('company.edit');
    Route::put('/company-info/{id}/update', [CompanyInfoController::class, 'update'])->name('company.update');
    Route::get('/general-setting', [GeneralSettingController::class, 'index'])->name('general-setting.index');

    Route::resource('/mata-uang', CurrencyController::class);
    Route::resource('/cash-bank', CashBankController::class);

    Route::prefix('token')->group(function () {
        Route::post('/unlock', [IdleController::class, 'unlock'])->name('token.unlock');
        Route::post('/expire', [IdleController::class, 'expireToken'])->name('token.expire');
        Route::get('/check', [IdleController::class, 'checkToken'])->name('token.check');
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/delete-read', [NotificationController::class, 'deleteRead'])->name('notifications.deleteRead');

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

    // Master Data
    Route::post('/customer/delete-multiple', [CustomerController::class, 'deleteMultiple']);
    Route::post('/customer/restore-multiple', [CustomerController::class, 'restoreMultiple']);
    Route::get('/customer/generate-id', [CustomerController::class, 'generateId']);
    Route::get('/customer/trash', [CustomerController::class, 'trash'])->name('customer.trash');
    Route::put('/customer/restore/{id}', [CustomerController::class, 'restore'])->name('customer.restore');
    Route::resource('customer', CustomerController::class);

    Route::post('/supplier/delete-multiple', [SupplierController::class, 'deleteMultiple']);
    Route::post('/supplier/restore-multiple', [SupplierController::class, 'restoreMultiple']);
    Route::get('/supplier/generate-id', [SupplierController::class, 'generateId']);
    Route::get('/supplier/trash', [SupplierController::class, 'trash'])->name('supplier.trash');
    Route::put('/supplier/restore/{id}', [SupplierController::class, 'restore'])->name('supplier.restore');
    Route::resource('supplier', SupplierController::class);

    Route::post('/salesman/delete-multiple', [SalesmanController::class, 'deleteMultiple']);
    Route::post('/salesman/restore-multiple', [SalesmanController::class, 'restoreMultiple']);
    Route::get('/salesman/generate-id', [SalesmanController::class, 'generateId']);
    Route::get('/salesman/trash', [SalesmanController::class, 'trash'])->name('salesman.trash');
    Route::put('/salesman/restore/{id}', [SalesmanController::class, 'restore'])->name('salesman.restore');
    Route::resource('salesman', SalesmanController::class);

    Route::post('/warehouse/delete-multiple', [WarehouseController::class, 'deleteMultiple']);
    Route::post('/warehouse/restore-multiple', [WarehouseController::class, 'restoreMultiple']);
    Route::get('/warehouse/generate-id', [WarehouseController::class, 'generateId']);
    Route::get('/warehouse/trash', [WarehouseController::class, 'trash'])->name('warehouse.trash');
    Route::put('/warehouse/restore/{id}', [WarehouseController::class, 'restore'])->name('warehouse.restore');
    Route::resource('warehouse', WarehouseController::class);

    Route::prefix('daftar-kendaraan')->name('daftar-kendaraan.')->group(function () {
        Route::get('/', [DaftarKendaraanController::class, 'index'])->name('index');
        Route::post('/store', [DaftarKendaraanController::class, 'store'])->name('store');
        Route::get('/data', [DaftarKendaraanController::class, 'data'])->name('data');
        Route::get('/trash', [DaftarKendaraanController::class, 'trash'])->name('trash');
        Route::get('/{id}/edit', [DaftarKendaraanController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [DaftarKendaraanController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/restore', [DaftarKendaraanController::class, 'restore'])->name('restore');
        Route::get('/{id}/detail', [DaftarKendaraanController::class, 'show'])->name('show');
        Route::post('/delete-multiple', [DaftarKendaraanController::class, 'deleteMultiple']);
        Route::post('/restore-multiple', [DaftarKendaraanController::class, 'restoreMultiple']);
    });
    Route::get('/data-barang/print-all', [DataBarangController::class, 'print_all'])->name('data-barang.print_all');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::post('/data-barang/delete-multiple', [DataBarangController::class, 'deleteMultiple']);
    Route::post('/data-barang/restore-multiple', [DataBarangController::class, 'restoreMultiple']);
    Route::get('/data-barang/trash', [DataBarangController::class, 'trash'])->name('data-barang.trash');
    Route::put('/data-barang/restore/{id}', [DataBarangController::class, 'restore'])->name('data-barang.restore');
    Route::resource('data-barang', DataBarangController::class);

    Route::post('/satuan-barang/delete-multiple', [SatuanBarangController::class, 'deleteMultiple']);
    Route::resource('satuan-barang', SatuanBarangController::class);

    Route::post('/kategori-barang/delete-multiple', [KategoriBarangController::class, 'deleteMultiple']);
    Route::resource('kategori-barang', KategoriBarangController::class);

    Route::get('/permintaan-pembelian/trash', [PurchaseRequisitionController::class, 'trash'])->name('permintaan-pembelian.trash');
    Route::get('/permintaan-pembelian/table-pr', [PurchaseRequisitionController::class, 'table_pr'])->name('permintaan-pembelian.table_pr');
    Route::post('/permintaan-pembelian/delete-multiple', [PurchaseRequisitionController::class, 'deleteMultiple']);
    Route::post('/permintaan-pembelian/restore-multiple', [PurchaseRequisitionController::class, 'restoreMultiple']);
    Route::put('/permintaan-pembelian/restore/{id}', [PurchaseRequisitionController::class, 'restore'])->name('permintaan-pembelian.restore');
    Route::get('/get-units-by-product/{id}', [PurchaseRequisitionController::class, 'getUnitsByProduct'])
        ->name('permintaan-pembelian.get_units');
    Route::post('/permintaan-pembelian/{id}/submit', [PurchaseRequisitionController::class, 'submitToPending'])->name('permintaan-pembelian.submit');
    Route::post('/permintaan-pembelian/change-status/{id}', [PurchaseRequisitionController::class, 'changeStatus'])
        ->name('permintaan-pembelian.change-status');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::get('/permintaan-pembelian/print/{id}', [PurchaseRequisitionController::class, 'print'])->name('permintaan-pembelian.print');
    Route::resource('permintaan-pembelian', PurchaseRequisitionController::class);

    Route::prefix('purchase-order')->name('purchase-order.')->group(function () {
        Route::get('/table-pr', [PurchaseOrderController::class, 'table_pr'])->name('table_pr');
        Route::get('/trash', [PurchaseOrderController::class, 'trash'])->name('trash');
        Route::resource('/', PurchaseOrderController::class);
    });

    Route::get('/sales-order/trash', [SalesOrderController::class, 'trash'])->name('sales-order.trash');
    Route::resource('sales-order', SalesOrderController::class);

});

/*
| Fallback (jika route tidak ditemukan)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    Log::warning('Fallback route triggered', [
        'url' => request()->fullUrl(),
        'user_id' => auth()->check() ? auth()->id() : null,
    ]);

    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return response()->view('errors.404', [], 404);
});

require __DIR__.'/auth.php';
