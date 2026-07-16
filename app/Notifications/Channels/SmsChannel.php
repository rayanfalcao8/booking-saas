<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $recipient = $notifiable->routeNotificationFor('sms', $notification);

        if (! is_string($recipient) || $recipient === '' || ! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $driver = (string) config('services.sms.driver', 'off');

        if ($driver === 'off') {
            return;
        }

        if ($driver === 'log') {
            Log::info('SMS notification', [
                'recipient' => $recipient,
                'message' => $message,
            ]);

            return;
        }

        if ($driver !== 'twilio') {
            throw new RuntimeException("Unsupported SMS driver [{$driver}].");
        }

        $this->sendWithTwilio($recipient, $message);
    }

    private function sendWithTwilio(string $recipient, string $message): void
    {
        $accountSid = (string) config('services.sms.twilio.account_sid');
        $authToken = (string) config('services.sms.twilio.auth_token');
        $from = (string) config('services.sms.twilio.from');
        $messagingServiceSid = (string) config('services.sms.twilio.messaging_service_sid');

        if ($accountSid === '' || $authToken === '' || ($from === '' && $messagingServiceSid === '')) {
            throw new RuntimeException('Twilio SMS credentials are incomplete.');
        }

        $payload = [
            'To' => $recipient,
            'Body' => $message,
        ];

        if ($messagingServiceSid !== '') {
            $payload['MessagingServiceSid'] = $messagingServiceSid;
        } else {
            $payload['From'] = $from;
        }

        Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->timeout(10)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", $payload)
            ->throw();
    }
}
