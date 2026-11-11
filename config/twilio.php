<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials required to authenticate with the Twilio REST API and
    | validate incoming webhook requests.
    |
    */
    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'phone_number' => env('TWILIO_PHONE_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Validation
    |--------------------------------------------------------------------------
    |
    | When enabled, all incoming requests to Twilio webhook routes are validated
    | using the X-Twilio-Signature header and the Twilio Security RequestValidator.
    |
    */
    'webhook_validation' => env('TWILIO_WEBHOOK_VALIDATION', env('APP_ENV') === 'production'),
];
