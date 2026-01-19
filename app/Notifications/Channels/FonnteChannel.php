<?php

namespace App\Notifications\Channels;

use App\Services\FonnteService;
use Illuminate\Notifications\Notification;

class FonnteChannel
{
    public function __construct(
        private readonly FonnteService $fonnte
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFonnte')) {
            return;
        }

        $message = $notification->{'toFonnte'}($notifiable);

        if (! $message || ! is_string($message)) {
            return;
        }

        $phone = $this->getPhoneNumber($notifiable);

        if (! $phone) {
            return;
        }

        $this->fonnte->sendMessage($phone, $message);
    }

    private function getPhoneNumber(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationForFonnte')) {
            return $notifiable->routeNotificationForFonnte();
        }

        return $notifiable->phone ?? null;
    }
}
