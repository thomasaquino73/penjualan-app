<?php

namespace App\Providers;

use App\Models\General\Company;
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

            $sistem = PengaturanSistem::first();
            $aplikasi = $sistem ? $sistem->nama_aplikasi : 'Default Aplication';
            $sistem = $sistem ? $sistem->nama_sistem : 'Default System';

            $view->with([
                'logo' => $logo,
                'favicon' => $favicon,
                'aplikasi' => $aplikasi,
                'sistem' => $sistem,
                'companyName' => $companyName,
                'website' => $website,
                'email' => $email,
                'notel' => $notel,
                'alamat' => $alamat,
            ]);
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
