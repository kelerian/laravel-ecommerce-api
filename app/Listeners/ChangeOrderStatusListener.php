<?php

namespace App\Listeners;

use App\Events\ChangeOrderStatusEvent;
use App\Notifications\ChangeOrderStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ChangeOrderStatusListener
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
    public function handle(ChangeOrderStatusEvent $event): void
    {
        $event->order
            ->notify(new ChangeOrderStatusNotification($event->newStatus,$event->oldStatus ));
    }
}
