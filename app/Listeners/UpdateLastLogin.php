<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastLogin
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
    public function handle(Login $event): void
    {
        try {
            /**
             * @var \App\Models\User $event
             */
            $event->user->last_login = now();
            $event->user->save();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
