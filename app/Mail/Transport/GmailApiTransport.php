<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailApiTransport extends AbstractTransport
{
    public function __construct(
        private readonly ?string $clientId,
        private readonly ?string $clientSecret,
        private readonly ?string $refreshToken,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (! $this->clientId || ! $this->clientSecret || ! $this->refreshToken) {
            throw new TransportException('Gmail API credentials are not fully configured.');
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        if (! $tokenResponse->successful()) {
            throw new TransportException(sprintf(
                'Gmail API access-token request failed with HTTP %d.',
                $tokenResponse->status(),
            ));
        }

        $accessToken = $tokenResponse->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new TransportException('Gmail API did not return an access token.');
        }

        $rawMessage = rtrim(strtr(base64_encode($message->toString()), '+/', '-_'), '=');

        $sendResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $rawMessage,
            ]);

        if (! $sendResponse->successful()) {
            throw new TransportException(sprintf(
                'Gmail API email send failed with HTTP %d: %s',
                $sendResponse->status(),
                (string) $sendResponse->json('error.message', 'unknown error'),
            ));
        }
    }

    public function __toString(): string
    {
        return 'gmail-api';
    }
}
