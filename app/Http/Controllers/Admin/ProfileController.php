<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IvrSetting;
use App\Services\TwilioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display user profile, security, and notification settings center.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $notificationSettings = [
            // In-app notifications
            'notify_in_app_enabled' => filter_var(IvrSetting::get('notify_in_app_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'notify_in_app_contact_requests' => filter_var(IvrSetting::get('notify_in_app_contact_requests', true), FILTER_VALIDATE_BOOLEAN),
            'notify_in_app_inbound_calls' => filter_var(IvrSetting::get('notify_in_app_inbound_calls', true), FILTER_VALIDATE_BOOLEAN),
            'notify_in_app_sms_activity' => filter_var(IvrSetting::get('notify_in_app_sms_activity', true), FILTER_VALIDATE_BOOLEAN),
            'notify_in_app_missed_calls' => filter_var(IvrSetting::get('notify_in_app_missed_calls', true), FILTER_VALIDATE_BOOLEAN),

            // Email notifications
            'notify_email_enabled' => filter_var(IvrSetting::get('notify_email_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'notify_email_address' => IvrSetting::get('notify_email_address', $user->email),
            'notify_email_contact_requests' => filter_var(IvrSetting::get('notify_email_contact_requests', true), FILTER_VALIDATE_BOOLEAN),
            'notify_email_daily_digest' => filter_var(IvrSetting::get('notify_email_daily_digest', false), FILTER_VALIDATE_BOOLEAN),

            // SMS alerts
            'notify_sms_enabled' => filter_var(IvrSetting::get('notify_sms_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'notify_sms_phone' => IvrSetting::get('notify_sms_phone', $user->phone ?? ''),
            'notify_sms_on_contact_request' => filter_var(IvrSetting::get('notify_sms_on_contact_request', true), FILTER_VALIDATE_BOOLEAN),

            // Sound alert
            'notify_sound_enabled' => filter_var(IvrSetting::get('notify_sound_enabled', true), FILTER_VALIDATE_BOOLEAN),
        ];

        $currentTab = $request->query('tab', 'profile');
        if (!in_array($currentTab, ['profile', 'security', 'notifications'])) {
            $currentTab = 'profile';
        }

        return view('pages.admin.profile.index', [
            'title' => 'Profile & Account Settings',
            'user' => $user,
            'settings' => $notificationSettings,
            'currentTab' => $currentTab,
        ]);
    }

    /**
     * Update personal profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:35'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Profile details successfully updated.');
    }

    /**
     * Update user password with secure current password verification.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('admin.profile', ['tab' => 'security'])
                ->withErrors(['current_password' => 'The current password provided is incorrect.'])
                ->withInput();
        }

        if (Hash::check($validated['password'], $user->password)) {
            return redirect()->route('admin.profile', ['tab' => 'security'])
                ->withErrors(['password' => 'The new password must be different from your current password.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.profile', ['tab' => 'security'])
            ->with('success', 'Password has been changed successfully.');
    }

    /**
     * Update dynamic notification channels and settings.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->validate([
            'notify_email_address' => ['nullable', 'email', 'max:255'],
            'notify_sms_phone' => ['nullable', 'string', 'max:35'],
        ]);

        $booleanKeys = [
            'notify_in_app_enabled',
            'notify_in_app_contact_requests',
            'notify_in_app_inbound_calls',
            'notify_in_app_sms_activity',
            'notify_in_app_missed_calls',
            'notify_email_enabled',
            'notify_email_contact_requests',
            'notify_email_daily_digest',
            'notify_sms_enabled',
            'notify_sms_on_contact_request',
            'notify_sound_enabled',
        ];

        foreach ($booleanKeys as $key) {
            $value = $request->has($key) ? '1' : '0';
            IvrSetting::set($key, $value, [
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => ucwords(str_replace(['notify_', '_'], ['', ' '], $key)),
            ]);
        }

        if ($request->filled('notify_email_address')) {
            IvrSetting::set('notify_email_address', $request->input('notify_email_address'), [
                'type' => 'text',
                'group' => 'notifications',
                'label' => 'Alert Email Address',
            ]);
        }

        if ($request->has('notify_sms_phone')) {
            IvrSetting::set('notify_sms_phone', $request->input('notify_sms_phone'), [
                'type' => 'text',
                'group' => 'notifications',
                'label' => 'Admin Alert Phone Number',
            ]);
        }

        return redirect()->route('admin.profile', ['tab' => 'notifications'])
            ->with('success', 'Notification preferences saved successfully.');
    }

    /**
     * Dispatch a test SMS alert notification.
     */
    public function sendTestNotification(Request $request, TwilioService $twilioService): RedirectResponse
    {
        $user = Auth::user();
        $targetPhone = IvrSetting::get('notify_sms_phone', $user->phone);

        if (empty($targetPhone)) {
            return redirect()->route('admin.profile', ['tab' => 'notifications'])
                ->withErrors(['notify_sms_phone' => 'Please provide an admin alert phone number first.']);
        }

        $message = "FlowCore Alert Test: Inbound telephony notification test dispatched successfully at " . now()->format('Y-m-d H:i:s');
        $result = $twilioService->sendSms($targetPhone, $message);

        if ($result['success']) {
            return redirect()->route('admin.profile', ['tab' => 'notifications'])
                ->with('success', "Test SMS notification successfully sent to {$targetPhone} (SID: {$result['message_sid']}).");
        }

        return redirect()->route('admin.profile', ['tab' => 'notifications'])
            ->withErrors(['test_alert' => "Failed to send test SMS: " . ($result['error'] ?? 'Unknown error')]);
    }
}
