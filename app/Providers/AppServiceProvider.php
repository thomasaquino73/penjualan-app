<?php

namespace App\Providers;

use App\Models\General\Company;
use App\Models\General\Currency;
use App\Models\PengaturanSistem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use URL;

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
        Paginator::useBootstrapFive();
        View::composer('*', function ($view) {

            $company = Company::first();

            $companyName = $company ? $company->nama_perusahaan : 'Default Company Name';
            $logo = $company && $company->logo ? asset($company->logo) : asset('image/no-images.jpg');
            $favicon = $company && $company->favicon ? asset($company->favicon) : asset('image/no-images.jpg');
            $alamat = $company ? $company->alamat : 'Default Company Address';
            $notel = $company ? $company->nomor_telepon : 'Default Company Phone Number';
            $email = $company ? $company->email : 'Default Company Email';
            $website = $company ? $company->website : 'Default Company Website';

            // 🔥 ambil semua currency (INI YANG KURANG)
            $currencies = Currency::all();

            // 🔥 currency aktif (navbar)
            $currency = session('currency_id')
                ? Currency::find(session('currency_id'))
                : $company?->defaultCurrency;

            $sistemData = PengaturanSistem::first();
            $aplikasi = $sistemData ? $sistemData->nama_aplikasi : 'Default Aplication';
            $sistem = $sistemData ? $sistemData->nama_sistem : 'Default System';

            $view->with([
                'mataUang' => $currency,
                'currencies' => $currencies, // ✅ FIX DISINI
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

        if (! function_exists('format_uang')) {
            function format_uang($amount)
            {
                return 'Rp '.number_format($amount, 0, ',', '.');
            }
        }
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
