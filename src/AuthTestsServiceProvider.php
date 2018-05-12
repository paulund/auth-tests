<?php

namespace Dappa\AuthTests;

use Illuminate\Support\ServiceProvider;

/**
 * Auth test service provider
 */
class AuthTestsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Stubs/tests/Feature/Auth/ForgotPasswordTest.php' => base_path('tests/Feature/Auth/ForgotPasswordTest.php'),
            __DIR__ . '/Stubs/tests/Feature/Auth/LoginTest.php' => base_path('tests/Feature/Auth/LoginTest.php'),
            __DIR__ . '/Stubs/tests/Feature/Auth/RegisterTest.php' => base_path('tests/Feature/Auth/RegisterTest.php'),
            __DIR__ . '/Stubs/tests/Feature/Auth/ResetPasswordTest.php' => base_path('tests/Feature/Auth/ResetPasswordTest.php'),
        ], 'auth-tests');
    }
}