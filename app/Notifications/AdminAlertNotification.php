<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class AdminAlertNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info', // info | success | warning | danger
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => $this->type,
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'data' => array_filter(['route' => $this->appRoute()]),
        ];
    }

    /**
     * $url is built with the web route() helper (e.g. https://app.test/schedules/5);
     * the mobile app's notification tap handler only needs the path part.
     */
    private function appRoute(): ?string
    {
        if (! $this->url) {
            return null;
        }

        return '/'.ltrim(parse_url($this->url, PHP_URL_PATH) ?? '', '/');
    }
}
