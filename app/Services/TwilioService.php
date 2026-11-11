<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

class TwilioService
{
    protected ?Client $client = null;
    protected ?string $accountSid;
    protected ?string $authToken;
    protected ?string $fromNumber;

    public function __construct()
    {
        $this->accountSid = config('twilio.account_sid');
        $this->authToken = config('twilio.auth_token');
        $this->fromNumber = config('twilio.phone_number');
    }

    /**
     * Get or initialize the Twilio Client instance.
     */
    public function getClient(): ?Client
    {
        if ($this->client === null && $this->accountSid && $this->authToken) {
            $this->client = new Client($this->accountSid, $this->authToken);
        }

        return $this->client;
    }

    /**
     * Send an SMS to a phone number.
     *
     * @param string $to The destination phone number in E.164 format.
     * @param string $message The SMS text message.
     * @param string|null $from The sender number (defaults to TWILIO_PHONE_NUMBER).
     * @return array [success => bool, message_sid => string|null, error => string|null]
     */
    public function sendSms(string $to, string $message, ?string $from = null): array
    {
        $fromNumber = $from ?: $this->fromNumber;

        if (empty($to)) {
            Log::warning('Twilio SMS: Attempted to send SMS without destination number');
            return [
                'success' => false,
                'message_sid' => null,
                'error' => 'Missing destination phone number',
            ];
        }

        if (empty($this->accountSid) || empty($this->authToken) || empty($fromNumber)) {
            Log::warning('Twilio SMS: Twilio credentials not configured in environment', [
                'to' => $this->maskPhoneNumber($to),
            ]);
            return [
                'success' => false,
                'message_sid' => null,
                'error' => 'Twilio credentials not configured',
            ];
        }

        try {
            $client = $this->getClient();
            $messageInstance = $client->messages->create(
                $to,
                [
                    'from' => $fromNumber,
                    'body' => $message,
                ]
            );

            Log::info('Twilio SMS sent successfully', [
                'sid' => $messageInstance->sid,
                'to' => $this->maskPhoneNumber($to),
            ]);

            return [
                'success' => true,
                'message_sid' => $messageInstance->sid,
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('Twilio SMS failed', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_sid' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate an incoming Twilio webhook request signature.
     */
    public function validateRequest(Request $request): bool
    {
        // If webhook validation is disabled (e.g., local testing or test suite)
        if (!config('twilio.webhook_validation', false)) {
            return true;
        }

        $signature = $request->header('X-Twilio-Signature');
        if (empty($signature) || empty($this->authToken)) {
            Log::warning('Twilio Webhook: Missing signature or auth token', [
                'has_signature' => !empty($signature),
                'has_token' => !empty($this->authToken),
            ]);
            return false;
        }

        try {
            $validator = new RequestValidator($this->authToken);
            $url = $request->fullUrl();
            $postData = $request->post();

            $isValid = $validator->validate($signature, $url, $postData);

            if (!$isValid) {
                Log::warning('Twilio Webhook: Signature validation failed', [
                    'url' => $url,
                ]);
            }

            return $isValid;
        } catch (Exception $e) {
            Log::error('Twilio Webhook: Exception during signature validation', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mask a phone number for privacy in logs.
     */
    protected function maskPhoneNumber(string $number): string
    {
        $length = strlen($number);
        if ($length <= 4) {
            return '****';
        }

        return substr($number, 0, 3) . str_repeat('*', $length - 6) . substr($number, -3);
    }
}
