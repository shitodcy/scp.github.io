<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Facades\Activity;

class LogFailedLoginAttempt
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
    public function handle(Failed $event): void
    {
        // Ambil username/email yang digunakan saat mencoba login
        $attemptedUsername = $event->credentials['username'] ?? ($event->credentials['password'] ?? 'N/A');
        
        // Catat aktivitas login yang gagal
        // Kita tidak mencatat passwordnya demi keamanan
        activity()
            ->useLog('warning') // <-- Mengatur level ke WARNING
            ->log("Failed login attempt for username/password: '{$attemptedUsername}'.");
    }
}
