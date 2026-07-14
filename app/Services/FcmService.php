<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Throwable;

class FcmService
{
    public function __construct(private Messaging $messaging) {}

    /**
     * Send a push notification to every device token registered for a user.
     * Tokens Firebase reports as invalid/unregistered are pruned automatically.
     *
     * @param  array<string, string>  $data  Extra payload (e.g. ['route' => '/schedules/5']).
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(FcmNotification::create($title, $body))
                ->withData(array_map('strval', $data));

            $report = $this->messaging->sendMulticast($message, $tokens);
            $this->pruneInvalidTokens($report);
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim push notification: '.$e->getMessage());
        }
    }

    private function pruneInvalidTokens(MulticastSendReport $report): void
    {
        $invalid = $report->invalidTokens();
        if (! empty($invalid)) {
            DeviceToken::whereIn('token', $invalid)->delete();
        }
    }
}
