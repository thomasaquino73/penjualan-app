<?php

use App\Providers\AppServiceProvider;
use Intervention\Image\Laravel\ServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;

return [
    AppServiceProvider::class,
    SocialiteServiceProvider::class,
    ServiceProvider::class,
];
