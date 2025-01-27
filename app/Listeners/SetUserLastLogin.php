<?php

namespace App\Listeners;

use App\Events\UserLoginProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SetUserLastLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserLoginProcess $event): void
    {
       $event->user?->update([
        'last_login_at' => now('Asia/Jakarta'),
       ]);
    }
}
