<?php

namespace App\Providers;

use App\Mail\Transport\GmailApiTransport;
use Illuminate\Support\Facades\Mail;
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
        Mail::extend('gmail-api', function (array $config): GmailApiTransport {
            $google = config('services.google');

            return new GmailApiTransport(
                $google['client_id'],
                $google['client_secret'],
                $google['gmail_refresh_token'],
            );
        });
    }
}
