<?php

namespace App\Providers;

use App\Models\PengaturanSistem;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();
        View::composer('*', function ($view) {
            $company = PengaturanSistem::first();
            $logo = $company && $company->logo ? asset($company->logo) : asset('image/no-images.jpg');
            $favicon = $company && $company->favicon ? asset($company->favicon) : asset('image/no-images.jpg');
            $aplikasi = $company ? $company->nama_aplikasi : 'Default Aplication';
            $sistem = $company ? $company->nama_sistem : 'Default System';
            $companyName = $company ? $company->nama_instansi : 'Default Company Name';
            $alamat = $company ? $company->alamat : 'Default Company Address';
            $notel = $company ? $company->no_telp : 'Default Company Phone Number';
            $email = $company ? $company->email : 'Default Company Email';
            $website = $company ? $company->website : 'Default Company Website';

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
            \URL::forceScheme('https');
        }
    }
}
