<?php

namespace App\Providers;

use App\Listeners\EventLoginUser;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\ServiceProvider;

class EventListenerProvider extends ServiceProvider
{


    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
