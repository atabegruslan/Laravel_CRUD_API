<?php

namespace App\Listeners;

use App\Events\NewEntryMade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewEntry;
use Notification;
use App\Models\User;

class HandleNewEntry
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewEntryMade  $event
     * @return void
     */
    public function handle(NewEntryMade $event)
    {
        Notification::send(
            User::all(), 
            new NewEntry([
                'entry_id'  => $event->attributes['id'],
                'entry_url' => url("/entry/" . $event->attributes['id']), 
                'name'      => $event->attributes['place'],
                'img_url'   => url('/images') . '/' . $event->attributes['img_url'],
            ])
        );
    }
}
