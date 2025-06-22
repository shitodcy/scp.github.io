<?php
namespace App\Listeners;
use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Facades\Activity;

class LogSuccessfulLogin {
    public function handle(Login $event): void {
        activity()->useLog('info')->log("User '{$event->user->username}' logged in successfully.");
    }
}