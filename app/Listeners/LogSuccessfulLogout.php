<?php
namespace App\Listeners;
use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\Facades\Activity;

class LogSuccessfulLogout {
    public function handle(Logout $event): void {
        if ($event->user) {
            activity()->useLog('info')->log("User '{$event->user->username}' logged out.");
        }
    }
}
