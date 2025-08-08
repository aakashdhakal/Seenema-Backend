<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;


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
        Response::macro('vttFile', function ($path) {
            return Response::file($path, ['Content-Type' => 'text/vtt']);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return env("FRONTEND_URL") . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
