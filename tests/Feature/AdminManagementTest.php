<?php

use App\Models\IvrOption;
use App\Models\IvrSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    $this->seed(\Database\Seeders\IvrSystemSeeder::class);
    $this->admin = User::where('email', 'admin@example.com')->first();
    Storage::fake('public');
});

test('unauthenticated users are redirected to login from admin routes', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));

    $response = $this->get(route('admin.ivr.audio.index'));
    $response->assertRedirect(route('admin.login'));

    $response = $this->get(route('admin.ivr.options.index'));
    $response->assertRedirect(route('admin.login'));

    $response = $this->get(route('admin.ivr.settings.index'));
    $response->assertRedirect(route('admin.login'));
});

test('admin can authenticate and access dashboard', function () {
    $response = $this->post(route('admin.login.submit'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($this->admin);

    $dashResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
    $dashResponse->assertStatus(200);
});

test('admin can view keypad options list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.ivr.options.index'));

    $response->assertStatus(200);
    $response->assertSee('Learn About FlowCore');
    $response->assertSee('FlowCore Presentation');
});

test('admin can create a new keypad option', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.ivr.options.store'), [
        'digit' => '6',
        'title' => 'Interview Scheduling',
        'description' => 'Instructions for scheduling interview',
        'action_type' => 'audio',
        'fallback_text' => 'Please visit our calendar to pick an interview time slot.',
        'is_active' => '1',
        'sort_order' => 6,
    ]);

    $response->assertRedirect(route('admin.ivr.options.index'));
    $this->assertDatabaseHas('ivr_options', [
        'digit' => '6',
        'title' => 'Interview Scheduling',
    ]);
});

test('duplicate digit for active option is rejected', function () {
    // Digit 1 is already an active option
    $response = $this->actingAs($this->admin)->post(route('admin.ivr.options.store'), [
        'digit' => '1',
        'title' => 'Duplicate Option',
        'action_type' => 'audio',
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors(['digit']);
});

test('admin can toggle option active status', function () {
    $option = IvrOption::where('digit', '1')->first();
    expect($option->is_active)->toBeTrue();

    // Toggle to disabled
    $response = $this->actingAs($this->admin)->post(route('admin.ivr.options.toggle', $option));
    $response->assertSessionHas('success');

    $option->refresh();
    expect($option->is_active)->toBeFalse();

    // Toggle back to active
    $this->actingAs($this->admin)->post(route('admin.ivr.options.toggle', $option));
    $option->refresh();
    expect($option->is_active)->toBeTrue();
});

test('admin can upload and replace audio recording for an option', function () {
    $option = IvrOption::where('digit', '1')->first();

    $audioFile = UploadedFile::fake()->create('presentation.mp3', 250, 'audio/mpeg');

    $response = $this->actingAs($this->admin)->post(route('admin.ivr.audio.upload'), [
        'target_type' => 'option',
        'option_id' => $option->id,
        'audio_file' => $audioFile,
    ]);

    $response->assertSessionHas('success');

    $option->refresh();
    expect($option->audio_path)->not->toBeNull()
        ->and($option->audio_path)->toContain('audio/')
        ->and($option->hasAudio())->toBeTrue()
        ->and($option->resolved_audio_url)->toContain('/audio/');

    // Clean up test file from public directory
    if (!empty($option->audio_path) && file_exists(public_path($option->audio_path))) {
        @unlink(public_path($option->audio_path));
    }
});

test('invalid file upload type is rejected', function () {
    $option = IvrOption::where('digit', '1')->first();

    // Text file disguised as audio or bad mime
    $badFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

    $response = $this->actingAs($this->admin)->post(route('admin.ivr.audio.upload'), [
        'target_type' => 'option',
        'option_id' => $option->id,
        'audio_file' => $badFile,
    ]);

    $response->assertSessionHasErrors(['audio_file']);
});

test('admin can update system settings and clear cache', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.ivr.settings.update'), [
        'max_retries' => 4,
        'gather_timeout' => 7,
        'return_to_menu_digit' => '9',
        'return_to_menu_prompt_text' => 'Press 9 to return to options.',
        'sms_enabled' => '1',
        'sms_message' => 'Custom recruitment SMS: https://example.com/jobs',
        'sms_confirmation_fallback_text' => 'SMS sent to your phone.',
        'welcome_fallback_text' => 'Custom welcome text',
        'main_presentation_fallback_text' => 'Custom main presentation',
        'menu_fallback_text' => '',
        'invalid_fallback_text' => 'Invalid key.',
        'no_input_fallback_text' => 'No key pressed.',
        'goodbye_fallback_text' => 'Goodbye.',
    ]);

    $response->assertSessionHas('success');

    expect(IvrSetting::get('max_retries'))->toBe('4')
        ->and(IvrSetting::get('gather_timeout'))->toBe('7')
        ->and(IvrSetting::get('sms_message'))->toBe('Custom recruitment SMS: https://example.com/jobs');
});
