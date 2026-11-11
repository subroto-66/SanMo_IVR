<?php

use App\Models\IvrSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    $this->seed(\Database\Seeders\IvrSystemSeeder::class);
    $this->admin = User::where('email', 'admin@example.com')->first();
});

test('unauthenticated users cannot access profile and are redirected to login', function () {
    $response = $this->get(route('admin.profile'));
    $response->assertRedirect(route('admin.login'));
});

test('authenticated admin can view profile page with all tabs', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.profile'));

    $response->assertStatus(200);
    $response->assertSee('Profile & Account Center', false);
    $response->assertSeeText('System Administrator');
    $response->assertSeeText('Profile Details');
    $response->assertSee('Security & Password', false);
    $response->assertSeeText('Dynamic Notification Settings');
});

test('admin can update personal profile information', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.profile.update'), [
        'name' => 'Alexander Vance',
        'email' => 'alexander@flowcore.com',
        'phone' => '+15554321098',
        'job_title' => 'Chief Telephony Director',
        'bio' => 'Overseeing all inbound recruitment calls and FlowCore marketing.',
    ]);

    $response->assertRedirect(route('admin.profile', ['tab' => 'profile']));
    $response->assertSessionHas('success');

    $this->admin->refresh();
    expect($this->admin->name)->toBe('Alexander Vance')
        ->and($this->admin->email)->toBe('alexander@flowcore.com')
        ->and($this->admin->phone)->toBe('+15554321098')
        ->and($this->admin->job_title)->toBe('Chief Telephony Director')
        ->and($this->admin->initials)->toBe('AV');
});

test('profile update validates required name and unique email', function () {
    User::factory()->create(['email' => 'otheruser@example.com']);

    $response = $this->actingAs($this->admin)->put(route('admin.profile.update'), [
        'name' => '',
        'email' => 'otheruser@example.com',
    ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('admin can change password with valid current password', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.profile.password'), [
        'current_password' => 'password',
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertRedirect(route('admin.profile', ['tab' => 'security']));
    $response->assertSessionHas('success');

    $this->admin->refresh();
    expect(Hash::check('NewSecurePassword123!', $this->admin->password))->toBeTrue();
});

test('password change fails when current password is wrong', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.profile.password'), [
        'current_password' => 'wrongpassword',
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertRedirect(route('admin.profile', ['tab' => 'security']));
    $response->assertSessionHasErrors(['current_password']);

    $this->admin->refresh();
    expect(Hash::check('password', $this->admin->password))->toBeTrue();
});

test('password change fails when confirmation does not match', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.profile.password'), [
        'current_password' => 'password',
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'Mismatch123!',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('admin can dynamically configure notification settings', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.profile.notifications'), [
        'notify_in_app_enabled' => '1',
        'notify_in_app_contact_requests' => '1',
        'notify_in_app_inbound_calls' => '1',
        'notify_sms_enabled' => '1',
        'notify_sms_phone' => '+14155552671',
        'notify_sms_on_contact_request' => '1',
        'notify_email_enabled' => '1',
        'notify_email_address' => 'recruitment.alerts@flowcore.com',
        'notify_email_contact_requests' => '1',
        'notify_sound_enabled' => '1',
    ]);

    $response->assertRedirect(route('admin.profile', ['tab' => 'notifications']));
    $response->assertSessionHas('success');

    expect(filter_var(IvrSetting::get('notify_sms_enabled'), FILTER_VALIDATE_BOOLEAN))->toBeTrue()
        ->and(IvrSetting::get('notify_sms_phone'))->toBe('+14155552671')
        ->and(IvrSetting::get('notify_email_address'))->toBe('recruitment.alerts@flowcore.com')
        ->and(filter_var(IvrSetting::get('notify_in_app_contact_requests'), FILTER_VALIDATE_BOOLEAN))->toBeTrue();
});

test('top level /profile redirects to admin.profile', function () {
    $response = $this->actingAs($this->admin)->get('/profile');
    $response->assertRedirect(route('admin.profile'));
});
