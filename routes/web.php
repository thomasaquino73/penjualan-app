<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\IdleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestEmailVerificationController;
use App\Http\Controllers\Inventory\Barang\DataBarangController;
use App\Http\Controllers\Inventory\Barang\KategoriBarangController;
use App\Http\Controllers\Inventory\Barang\SatuanBarangController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Purchase\ReceiveItemController;
use App\Http\Controllers\Purchase\Supplier\KategoriSupplierController;
use App\Http\Controllers\Purchase\Supplier\SupplierController;
use App\Http\Controllers\Sales\Customer\CustomerController;
use App\Http\Controllers\Sales\Customer\KategoriCustomerController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesQuotationController;
use App\Http\Controllers\Setting\CashBankController;
use App\Http\Controllers\Setting\Company\FobController;
use App\Http\Controllers\Setting\Company\ShippingController;
use App\Http\Controllers\Setting\Company\SyaratPembayaranController;
use App\Http\Controllers\Setting\CompanyDeliveryAddressController;
use App\Http\Controllers\Setting\CompanyInfoController;
use App\Http\Controllers\Setting\CurrencyController;
use App\Http\Controllers\Setting\ExchangeRateController;
use App\Http\Controllers\Setting\GeneralSettingController;
use App\Http\Controllers\Setting\PengaturanSistemController;
use App\Http\Controllers\Setting\PermissionsController;
use App\Http\Controllers\Setting\RolesController;
use App\Http\Controllers\Setting\UserController;
use App\Models\Setting\Company;
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

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

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

    // Route::post('/set-currency', function (Request $request) {
    //     session(['currency_id' => $request->currency_id]);

    //     return response()->json(['success' => true]);
    // })->name('set.currency');

    Route::post('/set-currency', function (Request $request) {
        try {
            $targetCurrencyId = $request->currency_id;
            $oldSession = session('currency_id');

            // Tetapkan session pilihan user sementara
            session(['currency_id' => $targetCurrencyId]);

            // Tes pemicu konversi helper. Jika rate kosong, ini akan melempar Exception
            convert_currency(1, $targetCurrencyId);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            // Ambil ID mata uang default perusahaan (IDR)
            $defaultCompanyCurrencyId = Company::first()->default_currency_id ?? 1;

            // Paksa kembalikan session ke IDR
            session(['currency_id' => $defaultCompanyCurrencyId]);

            // Kirim status false ke JavaScript, namun biarkan HTTP Status tetap 200
            return response()->json([
                'success' => false,
                'default_currency_id' => $defaultCompanyCurrencyId,
            ]);
        }
    })->name('set.currency');
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

    // MASTER DATA
    Route::post('/customer/delete-multiple', [CustomerController::class, 'deleteMultiple']);
    Route::post('/customer/restore-multiple', [CustomerController::class, 'restoreMultiple']);
    Route::get('/customer/generate-id', [CustomerController::class, 'generateId']);
    Route::get('/customer/trash', [CustomerController::class, 'trash'])->name('customer.trash');
    Route::put('/customer/restore/{id}', [CustomerController::class, 'restore'])->name('customer.restore');
    Route::resource('customer', CustomerController::class);
    Route::resource('kategori-customer', KategoriCustomerController::class);

    Route::get('/supplier/generate-id', [SupplierController::class, 'generateId']);
    Route::post('/supplier/delete-multiple', [SupplierController::class, 'deleteMultiple']);
    Route::post('/supplier/restore-multiple', [SupplierController::class, 'restoreMultiple']);
    Route::get('/supplier/trash', [SupplierController::class, 'trash'])->name('supplier.trash');
    Route::put('/supplier/restore/{id}', [SupplierController::class, 'restore'])->name('supplier.restore');
    Route::resource('supplier', SupplierController::class);
    Route::resource('kategori-supplier', KategoriSupplierController::class);

    Route::post('/warehouse/delete-multiple', [WarehouseController::class, 'deleteMultiple']);
    Route::post('/warehouse/restore-multiple', [WarehouseController::class, 'restoreMultiple']);
    Route::get('/warehouse/generate-id', [WarehouseController::class, 'generateId']);
    Route::get('/warehouse/trash', [WarehouseController::class, 'trash'])->name('warehouse.trash');
    Route::put('/warehouse/restore/{id}', [WarehouseController::class, 'restore'])->name('warehouse.restore');
    Route::resource('warehouse', WarehouseController::class);

    Route::get('/data-barang/print-all', [DataBarangController::class, 'print_all'])->name('data-barang.print_all');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::post('/data-barang/delete-multiple', [DataBarangController::class, 'deleteMultiple']);
    Route::post('/data-barang/restore-multiple', [DataBarangController::class, 'restoreMultiple']);
    Route::get('/data-barang/trash', [DataBarangController::class, 'trash'])->name('data-barang.trash');
    Route::put('/data-barang/restore/{id}', [DataBarangController::class, 'restore'])->name('data-barang.restore');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::resource('data-barang', DataBarangController::class);

    Route::post('/satuan-barang/delete-multiple', [SatuanBarangController::class, 'deleteMultiple']);
    Route::resource('satuan-barang', SatuanBarangController::class);

    Route::post('/kategori-barang/delete-multiple', [KategoriBarangController::class, 'deleteMultiple']);
    Route::resource('kategori-barang', KategoriBarangController::class);

    // PENGATURAN
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

    Route::resource('/mata-uang', CurrencyController::class);
    Route::resource('/cash-bank', CashBankController::class);
    Route::resource('/exchange-rate', ExchangeRateController::class);
    Route::resource('/company-delivery', CompanyDeliveryAddressController::class);
    Route::post('/shipping/delete-multiple', [ShippingController::class, 'deleteMultiple']);
    Route::post('/shipping/restore-multiple', [ShippingController::class, 'restoreMultiple']);
    Route::get('/shipping/trash', [ShippingController::class, 'trash'])->name('shipping.trash');
    Route::put('/shipping/restore/{id}', [ShippingController::class, 'restore'])->name('shipping.restore');
    Route::resource('/shipping', ShippingController::class);

    Route::post('/fob/delete-multiple', [FobController::class, 'deleteMultiple']);
    Route::resource('/fob', FobController::class);
    Route::resource('/syarat-pembayaran', SyaratPembayaranController::class);

    Route::get('/permintaan-pembelian/trash', [PurchaseRequisitionController::class, 'trash'])->name('permintaan-pembelian.trash');
    Route::get('/permintaan-pembelian/table-pr', [PurchaseRequisitionController::class, 'table_pr'])->name('permintaan-pembelian.table_pr');
    Route::post('/permintaan-pembelian/delete-multiple', [PurchaseRequisitionController::class, 'deleteMultiple']);
    Route::post('/permintaan-pembelian/restore-multiple', [PurchaseRequisitionController::class, 'restoreMultiple']);
    Route::put('/permintaan-pembelian/restore/{id}', [PurchaseRequisitionController::class, 'restore'])->name('permintaan-pembelian.restore');
    Route::get('/get-units-by-product/{id}', [PurchaseRequisitionController::class, 'getUnitsByProduct'])
        ->name('permintaan-pembelian.get_units');
    Route::post('/permintaan-pembelian/{id}/submit', [PurchaseRequisitionController::class, 'submitToPending'])->name('permintaan-pembelian.submit');
    Route::get('/permintaan-pembelian/print/{id}', [PurchaseRequisitionController::class, 'print'])->name('permintaan-pembelian.print');
    Route::resource('permintaan-pembelian', PurchaseRequisitionController::class);

    Route::prefix('purchase-order')->name('purchase-order.')->group(function () {
        Route::get('/trash', [PurchaseOrderController::class, 'trash'])->name('trash');
        Route::post('/delete-multiple', [PurchaseOrderController::class, 'deleteMultiple']);
        Route::post('/restore-multiple', [PurchaseOrderController::class, 'restoreMultiple']);
        Route::put('/restore/{id}', [PurchaseOrderController::class, 'restore'])->name('purchase-order.restore');
        Route::get('/get-product-price/{id}', [PurchaseOrderController::class, 'getPrice']);
        Route::get('/table-pr', [PurchaseOrderController::class, 'table_pr'])->name('table_pr');
        Route::get('/trash', [PurchaseOrderController::class, 'trash'])->name('trash');
        Route::get('/get-processing-requisitions', [PurchaseOrderController::class, 'getProcessingData'])->name('requisitions.processing');
        Route::post('/{id}/submit', [PurchaseOrderController::class, 'submitToPending'])->name('submit');
        Route::post('/change-status/{id}', [PurchaseOrderController::class, 'changeStatus']);
        Route::get('/print/{id}', [PurchaseOrderController::class, 'print'])->name('print');
        Route::get('/po/price-history', [PurchaseOrderController::class, 'getPriceHistory']);
        Route::get('/get-company-addresses/{companyId}', [PurchaseOrderController::class, 'getCompanyAddresses']);
        Route::post('/send-supplier/{id}', [PurchaseOrderController::class, 'sendSupplier'])->name('send-supplier');
        Route::post('/get-requisition-detail', [PurchaseOrderController::class, 'getRequisitionDetail'])->name('get-requisition-detail');
        Route::resource('', PurchaseOrderController::class)->parameters(['' => 'purchase_order']);
    });
    Route::prefix('receive-item')->name('receive-item.')->group(function () {
        Route::resource('', ReceiveItemController::class)->parameters(['' => 'receive_item']);
    });
    Route::post('/sales-order/send-supplier/{id}', [SalesOrderController::class, 'sendSupplier'])->name('sales-order.send-supplier');
    Route::post('/sales-order/change-status/{id}', [SalesOrderController::class, 'changeStatus']);
    Route::post('/sales-order/restore-multiple', [SalesOrderController::class, 'restoreMultiple']);
    Route::put('/sales-order/restore/{id}', [SalesOrderController::class, 'restore'])->name('sales-order.restore');
    Route::post('/sales-order/delete-multiple', [SalesOrderController::class, 'deleteMultiple']);
    Route::post('/sales-order/get-quotation-detail', [SalesOrderController::class, 'getQuotationDetail'])->name('sales-order.get-quotation-detail');
    Route::get('/sales-order/print/{id}', [SalesOrderController::class, 'print'])->name('sales-order.print');
    Route::get('/sales-order/sq/price-history', [SalesOrderController::class, 'getPriceHistory']);
    Route::get('/sales-order/trash', [SalesOrderController::class, 'trash'])->name('sales-order.trash');
    Route::get('/sales-order/get-processing-order', [SalesOrderController::class, 'getProcessingData'])->name('sales-order.quotation.processing');
    Route::post('/sales-order/{id}/submit', [SalesOrderController::class, 'submitToPending'])->name('sales-order.submit');
    Route::resource('sales-order', SalesOrderController::class);

    Route::get('/sales-quotation/print/{id}', [SalesQuotationController::class, 'print'])->name('sales-quotation.print');
    Route::get('/get-kontak/{customer_id}', [SalesQuotationController::class, 'getKontakByCustomer']);
    Route::get('/sales-quotation/sq/price-history', [SalesQuotationController::class, 'getPriceHistory']);
    Route::post('/sales-quotation/{id}/submit', [SalesQuotationController::class, 'submitToPending'])->name('sales-quotation.submit');
    Route::post('/sales-quotation/restore-multiple', [SalesQuotationController::class, 'restoreMultiple']);
    Route::put('/sales-quotation/restore/{id}', [SalesQuotationController::class, 'restore'])->name('sales-quotation.restore');
    Route::post('/sales-quotation/delete-multiple', [SalesQuotationController::class, 'deleteMultiple']);
    Route::get('/sales-quotation/trash', [SalesQuotationController::class, 'trash'])->name('sales-quotation.trash');
    Route::resource('sales-quotation', SalesQuotationController::class);
});

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
