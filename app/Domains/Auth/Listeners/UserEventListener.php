<?php

namespace App\Domains\Auth\Listeners;

use App\Domains\Auth\Events\UserLoggedIn;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Class UserEventListener.
 */
class UserEventListener
{
    /**
     * @param $event
     */
    public function onLoggedIn($event)
    {
        $login_at = now();
        $login_ip = request()->getClientIp();

        // Update the logging in users time & IP
        $event->user->update([
            'last_login_at' => $login_at,
            'last_login_ip' => $login_ip,
        ]);

        $event->user->loginHistories()->create([
            'login_at' => $login_at,
            'login_ip' => $login_ip
        ]);
    }

    /**
     * @param $event
     */
    public function onPasswordReset($event)
    {
        $event->user->update([
            'password_changed_at' => now(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            UserLoggedIn::class,
            'App\Domains\Auth\Listeners\UserEventListener@onLoggedIn'
        );

        $events->listen(
            PasswordReset::class,
            'App\Domains\Auth\Listeners\UserEventListener@onPasswordReset'
        );
    }
}
