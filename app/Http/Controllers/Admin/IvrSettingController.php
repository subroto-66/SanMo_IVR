<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IvrSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IvrSettingController extends Controller
{
    /**
     * Display IVR system configuration settings.
     */
    public function index(): View
    {
        $settings = [
            // Core IVR timing & limits
            'max_retries' => IvrSetting::get('max_retries', 3),
            'gather_timeout' => IvrSetting::get('gather_timeout', 5),
            'return_to_menu_digit' => IvrSetting::get('return_to_menu_digit', '9'),
            'return_to_menu_prompt_text' => IvrSetting::get('return_to_menu_prompt_text', 'To return to the main menu, press 9.'),

            // SMS Configuration
            'sms_enabled' => filter_var(IvrSetting::get('sms_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'sms_message' => IvrSetting::get('sms_message', 'Thank you for your interest! Visit our portal at: https://example.com/apply for more details.'),
            'sms_confirmation_fallback_text' => IvrSetting::get('sms_confirmation_fallback_text', 'You will receive the information by text message shortly.'),

            // Speech fallback texts
            'welcome_fallback_text' => IvrSetting::get('welcome_fallback_text', 'Welcome to our automated information and recruitment line.'),
            'main_presentation_fallback_text' => IvrSetting::get('main_presentation_fallback_text', 'Thank you for your interest in our company and career opportunities.'),
            'menu_fallback_text' => IvrSetting::get('menu_fallback_text', ''),
            'invalid_fallback_text' => IvrSetting::get('invalid_fallback_text', 'Sorry, that is not a valid selection. Please try again.'),
            'no_input_fallback_text' => IvrSetting::get('no_input_fallback_text', 'We did not receive a selection. Please choose an option.'),
            'goodbye_fallback_text' => IvrSetting::get('goodbye_fallback_text', 'Thank you for calling. Goodbye.'),

            // Twilio & Environment Credentials (Database stored with .env fallback)
            'twilio_account_sid' => IvrSetting::get('twilio_account_sid') ?: (config('twilio.account_sid') ?: env('TWILIO_ACCOUNT_SID', '')),
            'twilio_auth_token' => IvrSetting::get('twilio_auth_token') ?: (config('twilio.auth_token') ?: env('TWILIO_AUTH_TOKEN', '')),
            'twilio_phone_number' => IvrSetting::get('twilio_phone_number') ?: (config('twilio.phone_number') ?: env('TWILIO_PHONE_NUMBER', '')),
            'twilio_webhook_validation' => filter_var(IvrSetting::get('twilio_webhook_validation', config('twilio.webhook_validation', true)), FILTER_VALIDATE_BOOLEAN),
            'app_url' => IvrSetting::get('app_url') ?: (config('app.url') ?: env('APP_URL', 'http://localhost:8000')),
        ];

        return view('pages.admin.settings.index', [
            'title' => 'IVR System Settings',
            'settings' => $settings,
        ]);
    }

    /**
     * Update IVR system settings and environment credentials.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_retries' => ['required', 'integer', 'min:1', 'max:10'],
            'gather_timeout' => ['required', 'integer', 'min:2', 'max:30'],
            'return_to_menu_digit' => ['required', 'string', 'max:2'],
            'return_to_menu_prompt_text' => ['required', 'string', 'max:500'],
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_message' => ['required', 'string', 'max:1600'],
            'sms_confirmation_fallback_text' => ['required', 'string', 'max:500'],
            'welcome_fallback_text' => ['nullable', 'string', 'max:1000'],
            'main_presentation_fallback_text' => ['nullable', 'string', 'max:2000'],
            'menu_fallback_text' => ['nullable', 'string', 'max:2000'],
            'invalid_fallback_text' => ['nullable', 'string', 'max:500'],
            'no_input_fallback_text' => ['nullable', 'string', 'max:500'],
            'goodbye_fallback_text' => ['nullable', 'string', 'max:500'],

            // Environment & Twilio Credentials
            'twilio_account_sid' => ['nullable', 'string', 'max:100'],
            'twilio_auth_token' => ['nullable', 'string', 'max:100'],
            'twilio_phone_number' => ['nullable', 'string', 'max:50'],
            'twilio_webhook_validation' => ['nullable', 'boolean'],
            'app_url' => ['nullable', 'url', 'max:255'],
        ]);

        // 1. Update Standard IVR Settings
        $ivrSettingKeys = [
            'max_retries',
            'gather_timeout',
            'return_to_menu_digit',
            'return_to_menu_prompt_text',
            'sms_message',
            'sms_confirmation_fallback_text',
            'welcome_fallback_text',
            'main_presentation_fallback_text',
            'menu_fallback_text',
            'invalid_fallback_text',
            'no_input_fallback_text',
            'goodbye_fallback_text',
        ];

        foreach ($ivrSettingKeys as $key) {
            if (array_key_exists($key, $validated)) {
                IvrSetting::set($key, $validated[$key]);
            }
        }

        // Handle checkbox for boolean sms_enabled if not checked
        if (!$request->has('sms_enabled')) {
            IvrSetting::set('sms_enabled', '0');
        } else {
            IvrSetting::set('sms_enabled', '1');
        }

        // 2. Handle Twilio & Environment Credentials (stored safely in database to prevent dev-server process termination)
        if ($request->has('twilio_account_sid')) {
            $sid = trim((string) $request->input('twilio_account_sid'));
            IvrSetting::set('twilio_account_sid', $sid, ['group' => 'twilio', 'label' => 'Twilio Account SID']);
            config(['twilio.account_sid' => $sid]);
        }

        if ($request->filled('twilio_auth_token')) {
            $token = trim((string) $request->input('twilio_auth_token'));
            IvrSetting::set('twilio_auth_token', $token, ['group' => 'twilio', 'label' => 'Twilio Auth Token']);
            config(['twilio.auth_token' => $token]);
        }

        if ($request->has('twilio_phone_number')) {
            $phone = trim((string) $request->input('twilio_phone_number'));
            IvrSetting::set('twilio_phone_number', $phone, ['group' => 'twilio', 'label' => 'Twilio Phone Number']);
            config(['twilio.phone_number' => $phone]);
        }

        $webhookVal = $request->boolean('twilio_webhook_validation');
        IvrSetting::set('twilio_webhook_validation', $webhookVal ? '1' : '0', ['group' => 'twilio', 'label' => 'Twilio Webhook Validation']);
        config(['twilio.webhook_validation' => $webhookVal]);

        if ($request->filled('app_url')) {
            $appUrl = rtrim(trim((string) $request->input('app_url')), '/');
            IvrSetting::set('app_url', $appUrl, ['group' => 'general', 'label' => 'Application URL']);
            config(['app.url' => $appUrl]);
        }

        IvrSetting::clearCache();

        return back()->with('success', 'IVR system settings and credentials updated successfully.');
    }
}
