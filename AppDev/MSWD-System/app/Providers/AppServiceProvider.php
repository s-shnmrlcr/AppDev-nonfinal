<?php
// -shaine: Registers application-wide services and boot behavior.

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** -shaine: Registers application services in Laravel's dependency container. */
    public function register(): void
    {
        //
    }

    /** -shaine: Applies application-wide behavior after service registration. */
    public function boot(): void
    {
        //
    }
}
