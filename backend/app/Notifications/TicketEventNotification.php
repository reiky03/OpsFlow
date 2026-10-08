<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly array $data = [],
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            ...$this->data,
        ];
    }
}
