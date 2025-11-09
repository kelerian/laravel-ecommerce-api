<?php

namespace App\Notifications;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [60, 300];


    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $date = null
    )
    {
        $this->date = $date ?? now();
        $this->onQueue('emails');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteUrl = url('/');
        return (new MailMessage)
            ->subject('Уведомление о регистрации пользователя')
            ->view('emails.welcome_template', data: [
                'user' => $notifiable,
                'siteUrl' => $siteUrl,
                'registrationDate' => now()->format('d.m.Y'),
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
            'title' => 'Новая регистрация',
            'message' => 'Ваш аккаунт был успешно создан, информация отправлена на указанный email',
            'type' => 'registration',
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'date' => $this->date->format('j F Y'),
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
