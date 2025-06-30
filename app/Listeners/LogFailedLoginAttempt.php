<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Facades\Activity;

class LogFailedLoginAttempt
{

    public function __construct()
    {
        //
    }


    public function handle(Failed $event): void
    {

        $attemptedUsername = $event->credentials['username'] ?? ($event->credentials['password'] ?? 'N/A');


        activity()
            ->useLog('warning')
            ->log("Failed login attempt for username/password: '{$attemptedUsername}'.");
    }
}
