<?php

namespace App\Integrations\Notifications;

use App\Contracts\NotificationSender;
use App\Data\OutgoingNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Override;
use Throwable;

class TelegramNotificationSender implements NotificationSender
{
    #[Override]
    public function send(OutgoingNotification $message): bool
    {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.chat_id');

        if ($token === '' || $chatId === '') {
            Log::warning('Telegram message skipped: missing bot token or chat id.');

            return false;
        }

        $text = $message->title !== '' ? "{$message->title}\n{$message->body}" : $message->body;

        try {
            $response = Http::baseUrl((string) config('services.telegram.api_base', 'https://api.telegram.org'))
                ->timeout((int) config('services.telegram.timeout', 10))
                ->retry(3, 500, throw: false)
                ->acceptJson()
                ->asJson()
                ->post("/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'disable_web_page_preview' => false,
                ]);
        } catch (Throwable $e) {
            Log::warning('Telegram request failed', ['error' => $e->getMessage()]);

            return false;
        }

        if ($response->failed()) {
            Log::warning('Telegram returned non-2xx', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }
}
