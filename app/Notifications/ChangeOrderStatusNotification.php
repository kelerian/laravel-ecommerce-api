<?php

namespace App\Notifications;

use App\Models\Orders\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeOrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 3;
    public $timeout = 30;
    public $backoff = [60, 300];

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public OrderStatus $newStatus,
        public OrderStatus $oldStatus
    )
    {
        $this->onQueue('emails');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteUrl = url('/');
        return (new MailMessage)
            ->cc($notifiable->email)
            ->view('emails.change_status_order_template', data:[
                'order' => $notifiable,
                'newStatus' => $this->newStatus,
                'oldStatus' => $this->oldStatus,
                'siteUrl' => $siteUrl
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function failed(\Exception $exception)
    {
        \Log::channel('failed')->error("Failed to execute notification in queue", [
            'error' => $exception->getMessage(),
            'queue' => $this->queue,
        ]);
    }
}
