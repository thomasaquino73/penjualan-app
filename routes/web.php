<?php

use App\Http\Controllers\Archive\PembelianArsipController;
use App\Http\Controllers\Archive\PenjualanArsipController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\IdleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestEmailVerificationController;
use App\Http\Controllers\Inventory\Barang\BrandController;
use App\Http\Controllers\Inventory\Barang\DataBarangController;
use App\Http\Controllers\Inventory\Barang\KategoriBarangController;
use App\Http\Controllers\Inventory\Barang\SatuanBarangController;
use App\Http\Controllers\Inventory\ItemTransferController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Purchase\PurchaseDownPaymentController;
use App\Http\Controllers\Purchase\PurchaseInvoiceController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Purchase\ReceiveItemController;
use App\Http\Controllers\Purchase\Supplier\KategoriSupplierController;
use App\Http\Controllers\Purchase\Supplier\SupplierController;
use App\Http\Controllers\Sales\Customer\CustomerController;
use App\Http\Controllers\Sales\Customer\KategoriCustomerController;
use App\Http\Controllers\Sales\DeliveryOrderController;
use App\Http\Controllers\Sales\KasirController;
use App\Http\Controllers\Sales\ProformaInvoiceController;
use App\Http\Controllers\Sales\SalesDownPaymentController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesQuotationController;
use App\Http\Controllers\Setting\BankListController;
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

    Route::get('/dashboard/sales-statistics', [DashboardController::class, 'salesStatistics'])->name('dashboard.sales-statistics');
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
    Route::get('/warehouse/wh/get-stock', [WarehouseController::class, 'getStock'])->name('warehouse.wh.get-stock');
    Route::resource('warehouse', WarehouseController::class);

    Route::get('/stock-balance/{product}/{warehouse}', [DataBarangController::class, 'getStockBalance']);
    Route::get('/data-barang/print-stok', [DataBarangController::class, 'print_stok'])->name('data-barang.print_stok');
    Route::get('/data-barang/print-all', [DataBarangController::class, 'print_all'])->name('data-barang.print_all');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::post('/data-barang/delete-multiple', [DataBarangController::class, 'deleteMultiple']);
    Route::post('/data-barang/restore-multiple', [DataBarangController::class, 'restoreMultiple']);
    Route::get('/data-barang/trash', [DataBarangController::class, 'trash'])->name('data-barang.trash');
    Route::put('/data-barang/restore/{id}', [DataBarangController::class, 'restore'])->name('data-barang.restore');
    Route::get('/data-barang/print/{id}', [DataBarangController::class, 'print'])->name('data-barang.print');
    Route::resource('data-barang', DataBarangController::class);

    Route::prefix('ajax')->group(function () {
        Route::get('/products/{id}/units', [DataBarangController::class, 'getUnits'])
            ->name('ajax.products.units');

    });

    Route::post('/satuan-barang/delete-multiple', [SatuanBarangController::class, 'deleteMultiple']);
    Route::resource('satuan-barang', SatuanBarangController::class);

    Route::post('/brand/delete-multiple', [BrandController::class, 'deleteMultiple']);
    Route::resource('brand', BrandController::class);

    Route::post('/kategori-barang/delete-multiple', [KategoriBarangController::class, 'deleteMultiple']);
    Route::resource('kategori-barang', KategoriBarangController::class);

    // PENGATURAN
    Route::resource('roles', RolesController::class);
    Route::get('edit-roles', [RolesController::class, 'edit']);
    Route::patch('/restore-roles/{id}', [RolesController::class, 'restore']);
    Route::resource('permissions', PermissionsController::class);

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/cetak-semua-kartu/', [UserController::class, 'cetakAllBarcode'])->name('cetak.kartu.all');
        Route::get('/cetak-kartu/{id}', [UserController::class, 'cetak'])->name('cetak.kartu');
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
    Route::resource('/bank-list', BankListController::class);
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
    Route::get('/get-units-by-product/{id}', [PurchaseRequisitionController::class, 'getUnitsByProduct'])->name('permintaan-pembelian.get_units');
    Route::post('/permintaan-pembelian/{id}/submit', [PurchaseRequisitionController::class, 'submitToPending'])->name('permintaan-pembelian.submit');
    Route::patch('/permintaan-pembelian/{id}/close', [PurchaseRequisitionController::class, 'CloseDocument'])->name('permintaan-pembelian.close');
    Route::get('/permintaan-pembelian/print/{id}', [PurchaseRequisitionController::class, 'print'])->name('permintaan-pembelian.print');
    Route::resource('permintaan-pembelian', PurchaseRequisitionController::class);

    Route::prefix('purchase-order')->name('purchase-order.')->group(function () {
        Route::get('/{id}/data', [PurchaseOrderController::class, 'getSupplierData'])->name('getSupplierData');
        Route::patch('/{id}/close', [PurchaseOrderController::class, 'CloseDocument'])->name('close');
        Route::get('/trash', [PurchaseOrderController::class, 'trash'])->name('trash');
        Route::post('/delete-multiple', [PurchaseOrderController::class, 'deleteMultiple']);
        Route::post('/restore-multiple', [PurchaseOrderController::class, 'restoreMultiple']);
        Route::put('/restore/{id}', [PurchaseOrderController::class, 'restore'])->name('restore');
        Route::get('/get-product-price/{id}', [PurchaseOrderController::class, 'getPrice']);
        Route::get('/table-pr', [PurchaseOrderController::class, 'table_pr'])->name('table_pr');
        Route::get('/trash', [PurchaseOrderController::class, 'trash'])->name('trash');
        Route::get('/get-processing-requisitions', [PurchaseOrderController::class, 'getProcessingData'])->name('requisitions.processing');
        Route::post('/get-quotation-detail', [PurchaseOrderController::class, 'getQuotationDetail'])->name('getQuotationDetail');
        Route::post('/{id}/submit', [PurchaseOrderController::class, 'submitToPending'])->name('submit');
        Route::post('/{id}/process', [PurchaseOrderController::class, 'processData'])->name('process');
        Route::post('/change-status/{id}', [PurchaseOrderController::class, 'changeStatus']);
        Route::get('/print/{id}', [PurchaseOrderController::class, 'print'])->name('print');
        Route::get('/po/price-history', [PurchaseOrderController::class, 'getPriceHistory']);
        Route::get('/get-supplier-address/{supplier}', [PurchaseOrderController::class, 'getSupplierAddress']);
        // Route::get('/get-company-addresses/{companyId}', [PurchaseOrderController::class, 'getCompanyAddresses']);
        // Route::post('/send-supplier/{id}', [PurchaseOrderController::class, 'sendSupplier'])->name('send-supplier');
        Route::post('/get-requisition-detail', [PurchaseOrderController::class, 'getRequisitionDetail'])->name('get-requisition-detail');
        Route::resource('', PurchaseOrderController::class)->parameters(['' => 'purchase_order']);
    });
    Route::prefix('receive-item')->name('receive-item.')->group(function () {
        Route::get('/print/{id}', [ReceiveItemController::class, 'print'])->name('print');
        Route::post('/get-order-detail', [ReceiveItemController::class, 'getOrderDetail'])->name('get-order-detail');
        Route::get('/get-processing-po', [ReceiveItemController::class, 'getProcessingData'])->name('po.processing');
        Route::get('/trash', [ReceiveItemController::class, 'trash'])->name('trash');
        Route::put('restore/{id}', [ReceiveItemController::class, 'restore'])->name('restore');
        Route::resource('', ReceiveItemController::class)->parameters(['' => 'receive_item']);
    });

    Route::prefix('penjualan-toko')->name('penjualan-toko.')->group(function () {
        Route::get('/print/{id}', [KasirController::class, 'print'])->name('print');
        Route::put('restore/{id}', [KasirController::class, 'restore'])->name('restore');
        Route::get('/trash', [KasirController::class, 'trash'])->name('trash');
        Route::resource('', KasirController::class)->parameters(['' => 'penjualan_toko']);
    });

    Route::prefix('purchase-invoice')->name('purchase-invoice.')->group(function () {
        Route::get('/{id}/data', [PurchaseInvoiceController::class, 'getSupplierData'])->name('getSupplierData');
        Route::patch('/{id}/close', [PurchaseInvoiceController::class, 'CloseDocument'])->name('close');
        Route::get('/trash', [PurchaseInvoiceController::class, 'trash'])->name('trash');
        Route::post('/delete-multiple', [PurchaseInvoiceController::class, 'deleteMultiple']);
        Route::post('/restore-multiple', [PurchaseInvoiceController::class, 'restoreMultiple']);
        Route::put('/restore/{id}', [PurchaseInvoiceController::class, 'restore'])->name('restore');
        Route::get('/get-product-price/{id}', [PurchaseInvoiceController::class, 'getPrice']);
        Route::get('/table-pr', [PurchaseInvoiceController::class, 'table_pr'])->name('table_pr');
        Route::get('/trash', [PurchaseInvoiceController::class, 'trash'])->name('trash');
        Route::get('/get-processing-receive', [PurchaseInvoiceController::class, 'getProcessingData'])->name('receive.processing');
        Route::post('/get-receive-detail', [PurchaseInvoiceController::class, 'getReceiveDetail'])->name('getReceiveDetail');
        Route::post('/{id}/submit', [PurchaseInvoiceController::class, 'submitToPending'])->name('submit');
        Route::post('/{id}/process', [PurchaseInvoiceController::class, 'processData'])->name('process');
        Route::post('/change-status/{id}', [PurchaseInvoiceController::class, 'changeStatus']);
        Route::get('/print/{id}', [PurchaseInvoiceController::class, 'print'])->name('print');
        Route::get('/po/price-history', [PurchaseInvoiceController::class, 'getPriceHistory']);
        Route::get('/get-supplier-address/{supplier}', [PurchaseInvoiceController::class, 'getSupplierAddress']);
        // Route::get('/get-company-addresses/{companyId}', [PurchaseInvoiceController::class, 'getCompanyAddresses']);
        // Route::post('/send-supplier/{id}', [PurchaseInvoiceController::class, 'sendSupplier'])->name('send-supplier');
        Route::post('/get-requisition-detail', [PurchaseInvoiceController::class, 'getRequisitionDetail'])->name('get-requisition-detail');
        Route::resource('', PurchaseInvoiceController::class)->parameters(['' => 'purchase_invoice']);
    });

    Route::get('/sales-order/get-quotation/{customer}', [SalesOrderController::class, 'getQuotation'])->name('sales-order.getQuotation');
    Route::get('/sales-order/get-processing-order', [SalesOrderController::class, 'getProcessingData'])->name('sales-order.quotation.processing');
    // Route::post('/sales-order/get-quotation-detail-2', [SalesOrderController::class, 'getQuotationDetail2'])->name('sales-order.get-quotation-detail');
    Route::post('/sales-order/get-quotation-detail', [SalesOrderController::class, 'getQuotationDetail'])->name('sales-order.getQuotationDetail');
    Route::post('/sales-order/get-proforma-detail', [SalesOrderController::class, 'getProformaDetail'])->name('sales-order.get-proforma-detail');
    Route::get('/sales-order/{id}/data', [SalesOrderController::class, 'getCustomerData'])->name('sales-order.getCustomerData');
    Route::post('/sales-order/{id}/process', [SalesOrderController::class, 'processData'])->name('sales-order.process');
    // Route::get('/sales-order/get-units-by-product/{id}', [SalesOrderController::class, 'getUnitsByProduct'])->name('sales-order.get_units');
    Route::patch('/sales-order/{id}/close', [SalesOrderController::class, 'CloseDocument'])->name('sales-order.close');
    Route::post('/sales-order/restore-multiple', [SalesOrderController::class, 'restoreMultiple']);
    Route::put('/sales-order/restore/{id}', [SalesOrderController::class, 'restore'])->name('sales-order.restore');
    Route::post('/sales-order/delete-multiple', [SalesOrderController::class, 'deleteMultiple']);
    Route::get('/sales-order/print/{id}', [SalesOrderController::class, 'print'])->name('sales-order.print');
    Route::get('/sales-order/so/price-history', [SalesOrderController::class, 'getPriceHistory']);
    Route::get('/sales-order/trash', [SalesOrderController::class, 'trash'])->name('sales-order.trash');
    Route::resource('sales-order', SalesOrderController::class);

    Route::patch('/sales-quotation/{id}/close', [SalesQuotationController::class, 'CloseDocument'])->name('sales-quotation.close');
    Route::get('/sales-quotation/print/{id}', [SalesQuotationController::class, 'print'])->name('sales-quotation.print');
    Route::get('/get-kontak/{customer_id}', [SalesQuotationController::class, 'getKontakByCustomer']);
    Route::get('/sales-quotation/sq/price-history', [SalesQuotationController::class, 'getPriceHistory']);
    Route::post('/sales-quotation/{id}/submit', [SalesQuotationController::class, 'submitToPending'])->name('sales-quotation.submit');
    Route::post('/sales-quotation/restore-multiple', [SalesQuotationController::class, 'restoreMultiple']);
    Route::put('/sales-quotation/restore/{id}', [SalesQuotationController::class, 'restore'])->name('sales-quotation.restore');
    Route::post('/sales-quotation/delete-multiple', [SalesQuotationController::class, 'deleteMultiple']);
    Route::get('/sales-quotation/trash', [SalesQuotationController::class, 'trash'])->name('sales-quotation.trash');
    Route::resource('sales-quotation', SalesQuotationController::class);

    Route::get('/item-transfer/wh/get-stock', [ItemTransferController::class, 'getStock'])->name('item-transfer.wh.get-stock');
    Route::post('/item-transfer/change-status/{id}', [ItemTransferController::class, 'changeStatus']);
    Route::get('/item-transfer/print/{id}', [ItemTransferController::class, 'print'])->name('item-transfer.print');
    Route::post('/item-transfer/{id}/submit', [ItemTransferController::class, 'submitToProcess'])->name('item-transfer.submit');
    Route::put('/item-transfer/restore/{id}', [ItemTransferController::class, 'restore'])->name('item-transfer.restore');
    Route::get('/item-transfer/trash', [ItemTransferController::class, 'trash'])->name('item-transfer.trash');
    Route::resource('item-transfer', ItemTransferController::class);

    Route::get('/delivery-order/get-order/{customer}', [DeliveryOrderController::class, 'getQuotation'])->name('delivery-order.getQuotation');
    Route::post('/delivery-order/get-quotation-detail', [DeliveryOrderController::class, 'getQuotationDetail'])->name('delivery-order.getQuotationDetail');

    Route::get('/delivery-order/get-kontak/{customer_id}', [DeliveryOrderController::class, 'getKontakByCustomer']);
    Route::post('/delivery-order/get-order-detail', [DeliveryOrderController::class, 'getOrderDetail'])->name('delivery-order.get-order-detail');
    Route::get('/delivery-order/print/{id}', [DeliveryOrderController::class, 'print'])->name('delivery-order.print');
    Route::put('/delivery-order/restore/{id}', [DeliveryOrderController::class, 'restore'])->name('delivery-order.restore');
    Route::get('/delivery-order/trash', [DeliveryOrderController::class, 'trash'])->name('delivery-order.trash');
    Route::resource('delivery-order', DeliveryOrderController::class);

    // INVOICE
    Route::get('/sales-invoice/get-delivery/{customer}', [SalesInvoiceController::class, 'getDelivery'])->name('sales-invoice.getDelivery');
    Route::post('/sales-invoice/get-order-detail', [SalesInvoiceController::class, 'getDeliveryDetail'])->name('sales-invoice.getDeliveryDetail');
    Route::get('/sales-invoice/{id}/data', [SalesInvoiceController::class, 'getCustomerData'])->name('sales-invoice.getCustomerData');
    Route::post('/sales-invoice/{id}/process', [SalesInvoiceController::class, 'processData'])->name('sales-invoice.process');
    Route::post('/sales-invoice/restore-multiple', [SalesInvoiceController::class, 'restoreMultiple']);
    Route::put('/sales-invoice/restore/{id}', [SalesInvoiceController::class, 'restore'])->name('sales-invoice.restore');
    Route::post('/sales-invoice/delete-multiple', [SalesInvoiceController::class, 'deleteMultiple']);
    Route::get('/sales-invoice/print/{id}', [SalesInvoiceController::class, 'print'])->name('sales-invoice.print');
    Route::get('/sales-invoice/get-units-by-product/{id}', [SalesInvoiceController::class, 'getUnitsByProduct'])->name('sales-invoice.get_units');
    Route::get('/sales-invoice/si/price-history', [SalesInvoiceController::class, 'getPriceHistory']);
    Route::post('/sales-invoice/{id}/submit', [SalesInvoiceController::class, 'submitToPending'])->name('sales-invoice.submit');
    Route::get('/sales-invoice/trash', [SalesInvoiceController::class, 'trash'])->name('sales-invoice.trash');
    Route::resource('sales-invoice', SalesInvoiceController::class);

    Route::post('/proforma-invoice/get-quotation-detail', [ProformaInvoiceController::class, 'getQuotationDetail'])->name('proforma-invoice.get-quotation-detail');
    Route::get('/proforma-invoice/{id}/data', [ProformaInvoiceController::class, 'getCustomerData'])->name('proforma-invoice.getCustomerData');
    Route::get('/proforma-invoice/get-processing-order', [ProformaInvoiceController::class, 'getProcessingData'])->name('proforma-invoice.quotation.processing');
    Route::post('/proforma-invoice/{id}/process', [ProformaInvoiceController::class, 'processData'])->name('proforma-invoice.process');
    Route::post('/proforma-invoice/restore-multiple', [ProformaInvoiceController::class, 'restoreMultiple']);
    Route::put('/proforma-invoice/restore/{id}', [ProformaInvoiceController::class, 'restore'])->name('proforma-invoice.restore');
    Route::post('/proforma-invoice/delete-multiple', [ProformaInvoiceController::class, 'deleteMultiple']);
    Route::get('/proforma-invoice/print/{id}', [ProformaInvoiceController::class, 'print'])->name('proforma-invoice.print');
    Route::get('/proforma-invoice/get-units-by-product/{id}', [ProformaInvoiceController::class, 'getUnitsByProduct'])->name('proforma-invoice.get_units');
    Route::get('/proforma-invoice/sq/price-history', [ProformaInvoiceController::class, 'getPriceHistory']);
    Route::post('/proforma-invoice/{id}/submit', [ProformaInvoiceController::class, 'submitToPending'])->name('proforma-invoice.submit');
    Route::get('/proforma-invoice/trash', [ProformaInvoiceController::class, 'trash'])->name('proforma-invoice.trash');
    Route::resource('proforma-invoice', ProformaInvoiceController::class);
    Route::prefix('archive')->group(function () {
        Route::get('/arsip-purchase-requisition', [PembelianArsipController::class, 'indexPurchaseRequisition'])->name('archive.purchase-requisition');
        Route::get('arsip-purchase-requisition/datatable', [PembelianArsipController::class, 'tabelPurchaseRequisition'])->name('archive.purchase-requisition.datatable');
        Route::get('/arsip-purchase-requisition/{year}/print/{id}', [PembelianArsipController::class, 'printPurchaseRequisition'])->name('archive.purchase-requisition.print');

        Route::get('/arsip-purchase-order', [PembelianArsipController::class, 'indexPurchaseOrder'])->name('archive.purchase-order');
        Route::get('arsip-purchase-order/datatable', [PembelianArsipController::class, 'tabelPurchaseOrder'])->name('archive.purchase-order.datatable');
        Route::get('/arsip-purchase-order/{year}/print/{id}', [PembelianArsipController::class, 'printPurchaseOrder'])->name('archive.purchase-order.print');

        Route::get('/arsip-sales-order', [PenjualanArsipController::class, 'indexSalesOrder'])->name('archive.sales-order');
        Route::get('arsip-sales-order/datatable', [PenjualanArsipController::class, 'tabelSalesOrder'])->name('archive.sales-order.datatable');
        Route::get('/arsip-sales-order/{year}/print/{id}', [PenjualanArsipController::class, 'printSalesOrder'])->name('archive.sales-order.print');

        Route::get('/arsip-sales-quotation', [PenjualanArsipController::class, 'indexSalesQuotation'])->name('archive.sales-quotation');
        Route::get('arsip-sales-quotation/datatable', [PenjualanArsipController::class, 'tabelSalesQuotation'])->name('archive.sales-quotation.datatable');
        Route::get('/arsip-sales-quotation/{year}/print/{id}', [PenjualanArsipController::class, 'printSalesQuotation'])->name('archive.sales-quotation.print');

        Route::get('/arsip-proforma-invoice', [PenjualanArsipController::class, 'indexProformaInvoice'])->name('archive.proforma-invoice');
        Route::get('arsip-proforma-invoice/datatable', [PenjualanArsipController::class, 'tabelProformaInvoice'])->name('archive.proforma-invoice.datatable');
        Route::get('/arsip-proforma-invoice/{year}/print/{id}', [PenjualanArsipController::class, 'printProformaInvoice'])->name('archive.proforma-invoice.print');
    });

    Route::prefix('sales-down-payment')->name('sales-down-payment.')->group(function () {
        Route::put('/restore/{id}', [SalesDownPaymentController::class, 'restore'])->name('restore');
        Route::get('/print/{id}', [SalesDownPaymentController::class, 'print'])->name('print');
        Route::get('/ajax/customer-sales-order/{customer}', [SalesDownPaymentController::class, 'getSalesOrder'])->name('ajax.customer.sales-order');
        Route::get('/ajax/edit-customer-sales-order/{customer}', [SalesDownPaymentController::class, 'getSalesOrderEdit'])->name('ajax.customer.edit-sales-order');
        Route::get('/ajax/sales-order/{sales_order}/down-payment', [SalesDownPaymentController::class, 'getSalesOrderDownPayment'])->name('ajax.sales-order.down-payment');
        Route::get('/trash', [SalesDownPaymentController::class, 'trash'])->name('trash');
        Route::resource('', SalesDownPaymentController::class)->parameters(['' => 'sales-down-payment']);
    });

    Route::prefix('purchase-down-payment')->name('purchase-down-payment.')->group(function () {
        Route::put('/restore/{id}', [PurchaseDownPaymentController::class, 'restore'])->name('restore');
        Route::get('/print/{id}', [PurchaseDownPaymentController::class, 'print'])->name('print');
        Route::get('/ajax/supplier-purchase-order/{supplier}', [PurchaseDownPaymentController::class, 'getPurchaseOrder'])->name('ajax.supplier.purchase-order');
        Route::get('/ajax/edit-supplier-purchase-order/{supplier}', [PurchaseDownPaymentController::class, 'getPurchaseOrderEdit'])->name('ajax.supplier.edit-purchase-order');
        Route::get('/ajax/purchase-order/{purchase_order}/down-payment', [PurchaseDownPaymentController::class, 'getPurchaseOrderDownPayment'])->name('ajax.purchase-order.down-payment');
        Route::get('/trash', [PurchaseDownPaymentController::class, 'trash'])->name('trash');
        Route::resource('', PurchaseDownPaymentController::class)->parameters(['' => 'purchase-down-payment']);
    });
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
