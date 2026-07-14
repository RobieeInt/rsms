<?php

namespace App\Notifications\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;

/**
 * Custom Laravel notification channel: a Notification class opts in by
 * returning FcmChannel::class from its via() array and implementing
 * toFcm($notifiable): array{title: string, body: string, data?: array}.
 * Only App\Models\User notifiables have device tokens; anything else
 * (e.g. Client, sent by email) is silently skipped.
 */
class FcmChannel
{
    public function __construct(private FcmService $fcm) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notifiable, 'deviceTokens')) {
            return;
        }

        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $tokens = $notifiable->deviceTokens()->pluck('token')->all();
        if (empty($tokens)) {
            return;
        }

        $payload = $notification->toFcm($notifiable);

        $this->fcm->sendToTokens(
            $tokens,
            $payload['title'],
            $payload['body'],
            $payload['data'] ?? []
        );
    }
}
