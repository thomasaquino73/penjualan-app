<?php

namespace App\Providers;

use App\Models\PengaturanSistem;
use App\Models\Setting\Company;
use App\Models\Setting\Currency;
use App\Models\StockMutation;
use App\Observers\StockMutationObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        StockMutation::observe(StockMutationObserver::class);
        Paginator::useBootstrapFive();

        Schema::defaultStringLength(191);
        View::composer('*', function ($view) {
            // 1. Ambil data company sekalian dengan defaultCurrency-nya (Eager Loading)
            $company = Company::with('defaultCurrency')->first();

            $companyName = $company ? $company->nama_perusahaan : 'Default Company Name';
            $logo = $company && $company->logo ? asset($company->logo) : asset('image/no-images.jpg');
            $favicon = $company && $company->favicon ? asset($company->favicon) : asset('image/no-images.jpg');
            $alamat = $company ? $company->alamat : 'Default Company Address';
            $notel = $company ? $company->nomor_telepon : 'Default Company Phone Number';
            $email = $company ? $company->email : 'Default Company Email';
            $website = $company ? $company->website : 'Default Company Website';

            // 2. Ambil daftar semua currency untuk dropdown di navbar
            $currencies = Currency::all();

            // 3. Logika Currency Aktif yang Diperbaiki:
            $currency = null;

            // Cek apakah user sudah PERNAH mengganti currency secara manual via navbar dropdown
            // Kita gunakan nama session yang spesifik, misal 'user_selected_currency_id'
            if (session()->has('user_selected_currency_id')) {
                $currency = $currencies->firstWhere('id', session('user_selected_currency_id'));
            }

            // Jika user BELUM PERNAH memilih manual (atau session kosong),
            // maka WAJIB selalu mengikuti Default Currency dari table Company di database
            if (! $currency) {
                $currency = $company?->defaultCurrency;
            }

            // 4. Ambil data pengaturan sistem
            $sistemData = PengaturanSistem::first();
            $aplikasi = $sistemData ? $sistemData->nama_aplikasi : 'Default Application';
            $sistem = $sistemData ? $sistemData->nama_sistem : 'Default System';

            // Kirim semua variabel ke views
            $view->with([
                'company' => $company,
                'mataUang' => $currency,
                'currencies' => $currencies,
                'logo' => $logo,
                'favicon' => $favicon,
                'aplikasi' => $aplikasi,
                'sistem' => $sistem,
                'companyName' => $companyName,
                'website' => $website,
                'email' => $email,
                'notel' => $notel,
                'alamat' => $alamat,
                'globalCurrency' => $currency,

            ]);
        });

        // Global Helper: format_uang
        if (! function_exists('format_uang')) {
            function format_uang($amount)
            {
                return 'Rp '.number_format($amount, 0, ',', '.');
            }
        }

        // Force HTTPS di Production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
