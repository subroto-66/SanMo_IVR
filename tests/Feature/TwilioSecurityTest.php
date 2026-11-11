<?php

use App\Services\TwilioService;

beforeEach(function () {
    $this->seed(\Database\Seeders\IvrSystemSeeder::class);
});

test('webhook signature validation blocks unauthorized requests when enabled', function () {
    config([
        'twilio.webhook_validation' => true,
        'twilio.auth_token' => 'test_auth_token_12345',
    ]);

    // Request without signature header
    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_UNAUTHORIZED_TEST',
        'From' => '+15551234567',
    ]);

    $response->assertStatus(403);
});

test('webhook signature validation passes with valid signature', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('validateRequest')->once()->andReturn(true);
    $this->app->instance(TwilioService::class, $mockTwilio);

    config([
        'twilio.webhook_validation' => true,
        'twilio.auth_token' => 'test_auth_token_12345',
    ]);

    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_AUTHORIZED_TEST',
        'From' => '+15551234567',
    ], [
        'X-Twilio-Signature' => 'valid_signature_hash',
    ]);

    $response->assertStatus(200);
});

test('webhook routes do not require CSRF token', function () {
    // Calling /twilio/voice without CSRF token does not fail with 419 Page Expired
    config(['twilio.webhook_validation' => false]);

    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_CSRF_TEST',
        'From' => '+15551234567',
    ]);

    expect($response->status())->not->toBe(419);
    $response->assertStatus(200);
});
