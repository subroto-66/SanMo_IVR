<?php

use App\Models\IvrCallLog;
use App\Models\IvrOption;
use App\Models\IvrSetting;
use App\Services\TwilioService;

beforeEach(function () {
    // Ensure default settings & options exist
    $this->seed(\Database\Seeders\IvrSystemSeeder::class);
    // Disable signature validation for local tests
    config(['twilio.webhook_validation' => false]);
});

test('incoming call endpoint returns valid TwiML with FlowCore welcome and gather', function () {
    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_TEST_001',
        'From' => '+15551234567',
        'To' => '+15559876543',
        'Direction' => 'inbound',
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');

    $content = $response->getContent();
    expect($content)->toContain('<Response>')
        ->toContain('<Gather')
        ->toContain('numDigits="1"');

    // Verify call log was created
    $log = IvrCallLog::where('call_sid', 'CA_TEST_001')->first();
    expect($log)->not->toBeNull()
        ->and($log->from_number)->toBe('+15551234567');
});

test('opening sequence flows directly from welcome intro to main menu without playing main presentation', function () {
    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_TEST_OPENING_SEQ',
        'From' => '+15551234567',
        'To' => '+15559876543',
        'Direction' => 'inbound',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();

    // Verify Welcome / Intro is played
    expect($content)->toContain('Welcome to the FlowCore recruitment line.')
        // Verify Main Presentation is BYPASSED from automatic opening sequence
        ->not->toContain('FlowCore provides innovative opportunities and cutting edge programs')
        // Verify Main Menu prompt options are directly included
        ->toContain('Press 1 for Learn About FlowCore')
        ->toContain('Press 2 for Receive the FlowCore Presentation')
        ->toContain('Participation')
        ->toContain('Recruitment Process')
        ->toContain('Press 5 for Request Personal Contact')
        ->toContain('Press 0 to return to the main menu');
});

test('initial call plays separate opening welcome followed by main menu options with bargeIn enabled', function () {
    IvrSetting::set('welcome_audio_path', 'audio/system/flowcore-welcome.mp3');

    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_TEST_SEPARATE_OPENING',
        'From' => '+15551234567',
        'To' => '+15559876543',
        'Direction' => 'inbound',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();

    // Verify welcome audio URL is played inside Gather with bargeIn enabled
    expect($content)->toContain('<Gather')
        ->toContain('bargeIn="true"')
        ->toContain('<Play>')
        ->toContain('flowcore-welcome.mp3')
        // And Main Menu options immediately follow inside the same gather
        ->toContain('Press 1 for Learn About FlowCore');

    // Clean up
    IvrSetting::set('welcome_audio_path', null);
});

test('both custom welcome audio and custom menu audio play in sequence on initial inbound call', function () {
    IvrSetting::set('welcome_audio_path', 'audio/system/flowcore-welcome.mp3');
    IvrSetting::set('menu_prompt_audio_path', 'audio/system/flowcore-menu.mp3');

    $response = $this->post('/twilio/voice', [
        'CallSid' => 'CA_TEST_BOTH_AUDIO',
        'From' => '+15551234567',
        'To' => '+15559876543',
        'Direction' => 'inbound',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();

    expect($content)->toContain('<Gather')
        ->toContain('flowcore-welcome.mp3')
        ->toContain('flowcore-menu.mp3');

    // Clean up
    IvrSetting::set('welcome_audio_path', null);
    IvrSetting::set('menu_prompt_audio_path', null);
});

test('valid digit 1 plays option 1 confirmation audio and triggers SMS with learn link', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->once()
        ->with('+15551234567', Mockery::on(function ($msg) {
            return str_contains($msg, 'DiscoverFlowCore.com/learn');
        }))
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_001',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_002',
        'From' => '+15551234567',
        'Digits' => '1',
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');

    $content = $response->getContent();
    expect($content)->toContain('FlowCore')
        ->toContain('press 0')
        ->toContain('press pound');

    $log = IvrCallLog::where('call_sid', 'CA_TEST_002')->first();
    expect($log->selected_option)->toContain('1 — Learn FlowCore');
});

test('valid digit 2 triggers SMS with FlowCore presentation link and plays audio', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->once()
        ->with('+15551234567', Mockery::on(function ($msg) {
            return str_contains($msg, 'DiscoverFlowCore.com/presentation');
        }))
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_002',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_004',
        'From' => '+15551234567',
        'Digits' => '2',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('presentation');

    $log = IvrCallLog::where('call_sid', 'CA_TEST_004')->first();
    expect($log->selected_option)->toContain('2 — Presentation');
});

test('valid digit 3 triggers SMS with compensation link', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->once()
        ->with('+15551234567', Mockery::on(function ($msg) {
            return str_contains($msg, 'DiscoverFlowCore.com/opportunity');
        }))
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_003',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_OPT3',
        'From' => '+15551234567',
        'Digits' => '3',
    ]);

    $response->assertStatus(200);
    $log = IvrCallLog::where('call_sid', 'CA_TEST_OPT3')->first();
    expect($log->selected_option)->toContain('3 — Participation/Compensation');
});

test('valid digit 4 triggers SMS with next steps link', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->once()
        ->with('+15551234567', Mockery::on(function ($msg) {
            return str_contains($msg, 'DiscoverFlowCore.com/next');
        }))
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_004',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_OPT4',
        'From' => '+15551234567',
        'Digits' => '4',
    ]);

    $response->assertStatus(200);
    $log = IvrCallLog::where('call_sid', 'CA_TEST_OPT4')->first();
    expect($log->selected_option)->toContain('4 — Next Steps');
});

test('valid digit 5 requests personal contact and logs follow-up request', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->once()
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_005',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_OPT5',
        'From' => '+15551234567',
        'Digits' => '5',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('representative')
        ->toContain('follow up');

    $log = IvrCallLog::where('call_sid', 'CA_TEST_OPT5')->first();
    expect($log->selected_option)->toContain('5 — Contact Request')
        ->and($log->hasContactRequest())->toBeTrue()
        ->and($log->metadata['contact_requested'])->toBeTrue();
});

test('pressing return digit 0 returns caller directly to main FlowCore recruitment menu without replaying opening welcome', function () {
    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_003',
        'From' => '+15551234567',
        'Digits' => '0',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('<Gather')
        ->toContain('numDigits="1"')
        ->toContain('Press 1 for Learn About FlowCore')
        // Welcome intro must not be replayed when returning to menu
        ->not->toContain('Welcome to the FlowCore recruitment line');
});

test('pressing return digit 0 plays custom menu audio directly without replaying welcome audio', function () {
    IvrSetting::set('welcome_audio_path', 'audio/system/flowcore-welcome.mp3');
    IvrSetting::set('menu_prompt_audio_path', 'audio/system/flowcore-menu.mp3');

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_003_CUSTOM',
        'From' => '+15551234567',
        'Digits' => '0',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('<Gather')
        ->toContain('flowcore-menu.mp3')
        ->not->toContain('flowcore-welcome.mp3');

    // Clean up
    IvrSetting::set('welcome_audio_path', null);
    IvrSetting::set('menu_prompt_audio_path', null);
});

test('pressing digit 9 is now unrecognized and triggers invalid input prompt', function () {
    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_009_REMOVED',
        'From' => '+15551234567',
        'Digits' => '9',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('not a valid selection')
        ->toContain('<Gather')
        ->toContain('numDigits="1"');
});

test('pressing pound digit ends call with closing goodbye', function () {
    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_TEST_HASH',
        'From' => '+15551234567',
        'Digits' => '#',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('<Hangup/>');
});

test('sequential keypad presses build clear activity trail', function () {
    $mockTwilio = Mockery::mock(TwilioService::class);
    $mockTwilio->shouldReceive('sendSms')
        ->times(3)
        ->andReturn([
            'success' => true,
            'message_sid' => 'SM_MOCK_SEQ',
            'error' => null,
        ]);
    $this->app->instance(TwilioService::class, $mockTwilio);

    // 1st press: Option 2 (Presentation)
    $this->post('/twilio/menu', [
        'CallSid' => 'CA_SEQ_TEST',
        'From' => '+15551234567',
        'Digits' => '2',
    ]);

    // 2nd press: Option 3 (Compensation)
    $this->post('/twilio/menu', [
        'CallSid' => 'CA_SEQ_TEST',
        'From' => '+15551234567',
        'Digits' => '3',
    ]);

    // 3rd press: Option 4 (Next Steps)
    $this->post('/twilio/menu', [
        'CallSid' => 'CA_SEQ_TEST',
        'From' => '+15551234567',
        'Digits' => '4',
    ]);

    $log = IvrCallLog::where('call_sid', 'CA_SEQ_TEST')->first();
    expect($log->selected_option)->toContain('2 — Presentation')
        ->toContain('3 — Participation/Compensation')
        ->toContain('4 — Next Steps');

    $steps = $log->selection_steps;
    expect(count($steps))->toBe(3)
        ->and($steps[0]['label'])->toBe('Presentation')
        ->and($steps[1]['label'])->toBe('Participation/Compensation')
        ->and($steps[2]['label'])->toBe('Next Steps');
});

test('invalid keypad digit plays invalid selection message and retries menu', function () {
    $response = $this->post('/twilio/menu?retry=0', [
        'CallSid' => 'CA_TEST_005',
        'From' => '+15551234567',
        'Digits' => '8', // Digit 8 does not exist
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('not a valid selection')
        ->toContain('<Gather');
});

test('exceeding max retries with invalid input plays goodbye and hangs up', function () {
    $maxRetries = (int) IvrSetting::get('max_retries', 3);

    // Call with retry count at limit
    $response = $this->post("/twilio/menu?retry={$maxRetries}", [
        'CallSid' => 'CA_TEST_006',
        'From' => '+15551234567',
        'Digits' => '8',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('Goodbye')
        ->toContain('<Hangup/>');
});

test('no input / timeout plays timeout message and retries menu', function () {
    $response = $this->post('/twilio/menu?retry=0&timeout=1', [
        'CallSid' => 'CA_TEST_007',
        'From' => '+15551234567',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('did not receive a selection')
        ->toContain('<Gather');
});

test('exceeding timeout retries terminates call with goodbye', function () {
    $maxRetries = (int) IvrSetting::get('max_retries', 3);

    $response = $this->post("/twilio/menu?retry={$maxRetries}&timeout=1", [
        'CallSid' => 'CA_TEST_008',
        'From' => '+15551234567',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    expect($content)->toContain('Goodbye')
        ->toContain('<Hangup/>');
});

test('call status callback updates duration and completed status', function () {
    IvrCallLog::create([
        'call_sid' => 'CA_STATUS_TEST',
        'from_number' => '+15551234567',
        'status' => 'in-progress',
    ]);

    $response = $this->post('/twilio/status', [
        'CallSid' => 'CA_STATUS_TEST',
        'CallStatus' => 'completed',
        'CallDuration' => '78',
    ]);

    $response->assertStatus(200);

    $log = IvrCallLog::where('call_sid', 'CA_STATUS_TEST')->first();
    expect($log->status)->toBe('completed')
        ->and($log->duration)->toBe(78)
        ->and($log->ended_at)->not->toBeNull();
});

test('audio replacement changes playback dynamically without code changes', function () {
    $option = IvrOption::where('digit', '1')->first();
    $option->update([
        'audio_url' => 'https://storage.example.com/audio/new-recording-v2.mp3',
    ]);

    $response = $this->post('/twilio/menu', [
        'CallSid' => 'CA_AUDIO_TEST',
        'From' => '+15551234567',
        'Digits' => '1',
    ]);

    $response->assertStatus(200);
    $content = $response->getContent();
    // TwiML <Play> must contain the new URL immediately
    expect($content)->toContain('<Play>https://storage.example.com/audio/new-recording-v2.mp3</Play>');
});
