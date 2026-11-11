<?php

namespace App\Services;

use App\Jobs\SendIvrSmsJob;
use App\Models\IvrCallLog;
use App\Models\IvrOption;
use App\Models\IvrSetting;
use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\TwiML\VoiceResponse;

class IvrService
{
    protected TwilioService $twilioService;
    protected AudioStorageService $audioStorageService;

    public function __construct(TwilioService $twilioService, AudioStorageService $audioStorageService)
    {
        $this->twilioService = $twilioService;
        $this->audioStorageService = $audioStorageService;
    }

    /**
     * Build the initial inbound call response (Opening Welcome/Introduction -> Main Menu)
     * with barge-in enabled throughout so callers can press digits at any time.
     * Sequence: Caller Dials -> Opening Welcome/Intro -> Main Menu -> Caller Selects Option
     */
    public function buildInitialResponse(IvrCallLog $callLog): VoiceResponse
    {
        $response = new VoiceResponse();
        $timeout = (int) IvrSetting::get('gather_timeout', 5);
        $actionUrl = '/twilio/menu?retry=0';

        // Wrap Opening Welcome + Main Menu inside Gather with bargeIn enabled
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => $actionUrl,
            'method' => 'POST',
            'timeout' => $timeout,
            'bargeIn' => 'true',
            'finishOnKey' => '',
        ]);

        // Part 1: Opening Welcome/Introduction — plays when the call first begins
        $this->appendWelcomePromptToGather($gather);

        // Part 2: Main Menu — plays immediately afterward
        $this->appendMenuPromptsToGather($gather);

        // Timeout redirection if caller does not press any key within timeout
        $timeoutUrl = '/twilio/menu?retry=1&timeout=1';
        $response->redirect($timeoutUrl, ['method' => 'POST']);

        $callLog->recordEvent('welcome_and_menu_played');

        return $response;
    }

    /**
     * Append the opening welcome/introduction prompt (Audio or Say) to an existing Gather.
     */
    protected function appendWelcomePromptToGather($gather): void
    {
        $welcomeAudioPath = IvrSetting::get('welcome_audio_path');
        $welcomeAudioUrl = !empty($welcomeAudioPath) ? $this->audioStorageService->getUrl($welcomeAudioPath) : null;
        $welcomeText = IvrSetting::get('welcome_fallback_text', 'Welcome to the FlowCore recruitment line.');

        if (!empty($welcomeAudioUrl)) {
            $gather->play($welcomeAudioUrl);
        } elseif (!empty($welcomeText)) {
            $gather->say($welcomeText, ['voice' => 'Polly.Joanna']);
        }
    }

    /**
     * Build a standalone Main Menu response (for returning to menu or after retry).
     */
    public function buildMenuResponse(IvrCallLog $callLog, int $retryCount = 0): VoiceResponse
    {
        $response = new VoiceResponse();
        $this->appendMenuGather($response, $retryCount);

        $callLog->recordEvent('menu_presented', ['retry_count' => $retryCount]);

        return $response;
    }

    /**
     * Resolve human-readable activity label for caller keypad selections.
     */
    public function resolveActivityLabel(string $digit, ?IvrOption $option = null): string
    {
        return match ($digit) {
            '1' => '1 — Learn FlowCore',
            '2' => '2 — Presentation',
            '3' => '3 — Participation/Compensation',
            '4' => '4 — Next Steps',
            '5' => '5 — Contact Request',
            '0' => '0 — Return to Main Menu',
            '#' => '# — End Call',
            default => $option ? "{$option->digit} — {$option->title}" : "Digit {$digit}",
        };
    }

    /**
     * Process DTMF input from caller.
     */
    public function processInput(string $digit, ?string $fromNumber, IvrCallLog $callLog, int $retryCount = 0): VoiceResponse
    {
        $digit = trim($digit);
        $activityLabel = $this->resolveActivityLabel($digit);
        $maxRetries = (int) IvrSetting::get('max_retries', 3);

        $callLog->recordEvent('dtmf_received', [
            'digit' => $digit,
            'activity' => $activityLabel,
            'retry_count' => $retryCount,
        ]);

        // Keypad #: Immediate End Call command
        if ($digit === '#') {
            $callLog->appendActivity('# — End Call', ['action' => 'end_call']);
            return $this->buildGoodbyeResponse($callLog, 'caller_pressed_hash');
        }

        // Keypad 0: Return to Main Menu command
        if ($digit === '0') {
            $callLog->appendActivity('0 — Return to Main Menu', ['action' => 'return_to_main_menu']);
            return $this->buildMenuResponse($callLog, 0);
        }

        // Look up active option by digit (1, 2, 3, 4, 5)
        $option = IvrOption::active()->where('digit', $digit)->first();

        if ($option) {
            $label = $this->resolveActivityLabel($digit, $option);
            $callLog->appendActivity($label, ['action_type' => $option->action_type]);
            return $this->executeOption($option, $fromNumber, $callLog);
        }

        // Invalid digit pressed
        return $this->handleInvalidInput($callLog, $retryCount + 1, $maxRetries);
    }

    /**
     * Execute the selected IVR option action.
     */
    protected function executeOption(IvrOption $option, ?string $fromNumber, IvrCallLog $callLog): VoiceResponse
    {
        // Case 1: Goodbye Action
        if ($option->action_type === 'goodbye') {
            return $this->buildGoodbyeResponse($callLog, 'option_selected');
        }

        // Case 2: Menu Action
        if ($option->action_type === 'menu') {
            return $this->buildMenuResponse($callLog, 0);
        }

        // Case 3: Contact Request Action (Option 5)
        if ($option->action_type === 'contact_request' || $option->digit === '5') {
            return $this->handleContactRequestOption($option, $fromNumber, $callLog);
        }

        // Case 4: SMS Action (Options 1, 2, 3, 4)
        if ($option->action_type === 'sms' || $option->sms_enabled) {
            return $this->handleSmsOption($option, $fromNumber, $callLog);
        }

        // Case 5: Audio Information Playback with barge-in support
        $response = new VoiceResponse();
        $audioUrl = $option->resolved_audio_url;
        $timeout = (int) IvrSetting::get('gather_timeout', 5);
        $returnPrompt = IvrSetting::get(
            'return_to_menu_prompt_text',
            'To return to the main menu, press 0. To end your call, press pound.'
        );

        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/twilio/menu?retry=0',
            'method' => 'POST',
            'timeout' => $timeout,
            'bargeIn' => 'true',
            'finishOnKey' => '',
        ]);

        if (!empty($audioUrl)) {
            $gather->play($audioUrl);
        } elseif (!empty($option->fallback_text)) {
            $gather->say($option->fallback_text, ['voice' => 'Polly.Joanna']);
        } else {
            $gather->say("You selected {$option->title}.", ['voice' => 'Polly.Joanna']);
        }

        $gather->say($returnPrompt, ['voice' => 'Polly.Joanna']);

        // Default back to menu if no input within timeout
        $response->redirect('/twilio/menu?action=menu', ['method' => 'POST']);

        return $response;
    }

    /**
     * Handle Personal Contact Request (Option 5).
     * Dispatches confirmation and admin alert SMS in the background.
     */
    protected function handleContactRequestOption(IvrOption $option, ?string $fromNumber, IvrCallLog $callLog): VoiceResponse
    {
        $response = new VoiceResponse();

        // 1. Record contact request in call log audit trail and metadata
        $callLog->recordEvent('contact_requested', [
            'caller_phone' => $fromNumber ?: 'Unknown',
            'requested_at' => \Carbon\Carbon::now()->toIso8601String(),
            'status' => 'pending_followup',
            'option' => '5 — Contact Request',
        ]);

        $metadata = $callLog->metadata ?? [];
        $metadata['has_contact_request'] = true;
        $metadata['contact_requested'] = true;
        $metadata['contact_request'] = [
            'caller_phone' => $fromNumber ?: 'Unknown',
            'requested_at' => \Carbon\Carbon::now()->toIso8601String(),
            'status' => 'pending_followup',
        ];
        $callLog->metadata = $metadata;
        $callLog->save();

        Log::info("FlowCore Contact Request recorded from {$fromNumber} (Call SID: {$callLog->call_sid})");

        // 2. Asynchronously dispatch caller confirmation SMS in background (zero voice latency)
        $smsEnabled = filter_var(IvrSetting::get('sms_enabled', true), FILTER_VALIDATE_BOOLEAN);
        if ($smsEnabled && !empty($fromNumber) && !empty($option->sms_message)) {
            $this->dispatchSmsJob(
                $fromNumber,
                $option->sms_message,
                $callLog->id,
                'contact_confirmation',
                '5',
                $callLog->to_number
            );
        }

        // 3. Asynchronously dispatch staff alert SMS in background
        $notifySmsEnabled = filter_var(IvrSetting::get('notify_sms_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $notifyOnContact = filter_var(IvrSetting::get('notify_sms_on_contact_request', true), FILTER_VALIDATE_BOOLEAN);
        $adminPhone = IvrSetting::get('notify_sms_phone');
        if ($notifySmsEnabled && $notifyOnContact && !empty($adminPhone) && !empty($fromNumber)) {
            $adminAlert = "FlowCore Alert: Personal contact request received from {$fromNumber}.";
            $this->dispatchSmsJob(
                $adminPhone,
                $adminAlert,
                $callLog->id,
                'admin_alert',
                '5',
                $callLog->to_number
            );
        }

        // 4. Single Gather with confirmation + navigation prompt
        $timeout = (int) IvrSetting::get('gather_timeout', 5);
        $returnPrompt = IvrSetting::get(
            'return_to_menu_prompt_text',
            'To return to the main menu, press 0. To end your call, press pound.'
        );

        $confirmationText = !empty($option->fallback_text)
            ? $option->fallback_text
            : 'Thank you. Your request for personal contact has been received. A FlowCore representative will follow up with you shortly.';

        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/twilio/menu?retry=0',
            'method' => 'POST',
            'timeout' => $timeout,
            'bargeIn' => 'true',
            'finishOnKey' => '',
        ]);

        $audioUrl = $option->resolved_audio_url;
        if (!empty($audioUrl)) {
            $gather->play($audioUrl);
        } else {
            $gather->say($confirmationText, ['voice' => 'Polly.Joanna']);
        }

        $gather->say($returnPrompt, ['voice' => 'Polly.Joanna']);

        // Default back to menu if no input within timeout
        $response->redirect('/twilio/menu?action=menu', ['method' => 'POST']);

        return $response;
    }

    /**
     * Handle SMS Action with Confirmation Audio/TTS (Options 1, 2, 3, 4).
     * Dispatches SMS in the background so audio playback begins immediately.
     */
    protected function handleSmsOption(IvrOption $option, ?string $fromNumber, IvrCallLog $callLog): VoiceResponse
    {
        $response = new VoiceResponse();

        $smsEnabled = filter_var(IvrSetting::get('sms_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $smsMessage = !empty($option->sms_message)
            ? $option->sms_message
            : IvrSetting::get('sms_message', 'Thank you for your interest in FlowCore! Learn more at: https://DiscoverFlowCore.com/learn');

        $confirmationText = !empty($option->fallback_text)
            ? $option->fallback_text
            : IvrSetting::get('sms_confirmation_fallback_text', 'You will receive the information and link by text message shortly.');

        // Dispatch SMS in background (zero voice latency)
        if ($smsEnabled && !empty($fromNumber)) {
            $this->dispatchSmsJob(
                $fromNumber,
                $smsMessage,
                $callLog->id,
                'option_sms',
                $option->digit,
                $callLog->to_number
            );

            $callLog->recordEvent('sms_queued', [
                'to' => $fromNumber,
                'option' => $option->digit,
            ]);
        } else {
            $callLog->recordEvent('sms_skipped', [
                'sms_enabled' => $smsEnabled,
                'has_from_number' => !empty($fromNumber),
            ]);
        }

        // Single Gather with confirmation + navigation prompt
        $timeout = (int) IvrSetting::get('gather_timeout', 5);
        $returnPrompt = IvrSetting::get(
            'return_to_menu_prompt_text',
            'To return to the main menu, press 0. To end your call, press pound.'
        );

        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/twilio/menu?retry=0',
            'method' => 'POST',
            'timeout' => $timeout,
            'bargeIn' => 'true',
            'finishOnKey' => '',
        ]);

        $audioUrl = $option->resolved_audio_url;
        if (!empty($audioUrl)) {
            $gather->play($audioUrl);
        } else {
            $gather->say($confirmationText, ['voice' => 'Polly.Joanna']);
        }

        $gather->say($returnPrompt, ['voice' => 'Polly.Joanna']);

        // Default back to menu if no input within timeout
        $response->redirect('/twilio/menu?action=menu', ['method' => 'POST']);

        return $response;
    }

    /**
     * Handle invalid keypad selection.
     */
    public function handleInvalidInput(IvrCallLog $callLog, int $newRetryCount, int $maxRetries): VoiceResponse
    {
        $response = new VoiceResponse();

        $callLog->recordEvent('invalid_digit_pressed', [
            'retry_count' => $newRetryCount,
            'max_retries' => $maxRetries,
        ]);

        if ($newRetryCount >= $maxRetries) {
            return $this->buildGoodbyeResponse($callLog, 'max_invalid_retries_exceeded');
        }

        $invalidAudioPath = IvrSetting::get('invalid_input_audio_path');
        $invalidAudioUrl = !empty($invalidAudioPath) ? $this->audioStorageService->getUrl($invalidAudioPath) : null;
        $invalidText = IvrSetting::get('invalid_fallback_text', 'Sorry, that is not a valid selection. Please try again.');

        if (!empty($invalidAudioUrl)) {
            $response->play($invalidAudioUrl);
        } else {
            $response->say($invalidText, ['voice' => 'Polly.Joanna']);
        }

        $this->appendMenuGather($response, $newRetryCount);

        return $response;
    }

    /**
     * Handle timeout / no input entered.
     */
    public function handleTimeout(IvrCallLog $callLog, int $newRetryCount): VoiceResponse
    {
        $response = new VoiceResponse();
        $maxRetries = (int) IvrSetting::get('max_retries', 3);

        $callLog->recordEvent('no_input_timeout', [
            'retry_count' => $newRetryCount,
            'max_retries' => $maxRetries,
        ]);

        if ($newRetryCount >= $maxRetries) {
            return $this->buildGoodbyeResponse($callLog, 'max_timeout_retries_exceeded');
        }

        $noInputAudioPath = IvrSetting::get('no_input_audio_path');
        $noInputAudioUrl = !empty($noInputAudioPath) ? $this->audioStorageService->getUrl($noInputAudioPath) : null;
        $noInputText = IvrSetting::get('no_input_fallback_text', 'We did not receive a selection. Please choose an option.');

        if (!empty($noInputAudioUrl)) {
            $response->play($noInputAudioUrl);
        } else {
            $response->say($noInputText, ['voice' => 'Polly.Joanna']);
        }

        $this->appendMenuGather($response, $newRetryCount);

        return $response;
    }

    /**
     * Build goodbye response and terminate call.
     */
    public function buildGoodbyeResponse(IvrCallLog $callLog, string $reason = 'normal'): VoiceResponse
    {
        $response = new VoiceResponse();

        $goodbyeAudioPath = IvrSetting::get('goodbye_audio_path');
        $goodbyeAudioUrl = !empty($goodbyeAudioPath) ? $this->audioStorageService->getUrl($goodbyeAudioPath) : null;
        $goodbyeText = IvrSetting::get('goodbye_fallback_text', 'Thank you for calling FlowCore. Goodbye.');

        if (!empty($goodbyeAudioUrl)) {
            $response->play($goodbyeAudioUrl);
        } else {
            $response->say($goodbyeText, ['voice' => 'Polly.Joanna']);
        }

        $response->hangup();

        $callLog->recordEvent('call_ended_goodbye', ['reason' => $reason]);

        return $response;
    }

    /**
     * Append menu prompts to an existing Gather instance.
     */
    protected function appendMenuPromptsToGather($gather): void
    {
        $menuPromptAudioPath = IvrSetting::get('menu_prompt_audio_path');
        $menuPromptAudioUrl = !empty($menuPromptAudioPath) ? $this->audioStorageService->getUrl($menuPromptAudioPath) : null;

        if (!empty($menuPromptAudioUrl)) {
            $gather->play($menuPromptAudioUrl);
        } else {
            $menuPromptText = IvrSetting::get('menu_fallback_text');

            if (!empty($menuPromptText)) {
                $gather->say($menuPromptText, ['voice' => 'Polly.Joanna']);
            } else {
                // Generate menu text dynamically from active database options (excluding 9, 0, #)
                $activeOptions = IvrOption::active()->whereNotIn('digit', ['9', '0', '#'])->ordered()->get();
                $promptLines = [];
                foreach ($activeOptions as $opt) {
                    $promptLines[] = "Press {$opt->digit} for {$opt->title}.";
                }

                $promptLines[] = "Press 0 to return to the main menu. Press pound to end your call.";

                $dynamicPrompt = !empty($promptLines)
                    ? implode(' ', $promptLines)
                    : 'Please select an option from your keypad.';

                $gather->say($dynamicPrompt, ['voice' => 'Polly.Joanna']);
            }
        }
    }

    /**
     * Append a Gather element with active menu options, barge-in, and fallback timeout handling.
     */
    protected function appendMenuGather(VoiceResponse $response, int $retryCount = 0): void
    {
        $timeout = (int) IvrSetting::get('gather_timeout', 5);
        $actionUrl = '/twilio/menu?retry=' . $retryCount;

        $gather = $response->gather([
            'numDigits' => 1,
            'action' => $actionUrl,
            'method' => 'POST',
            'timeout' => $timeout,
            'bargeIn' => 'true',
            'finishOnKey' => '',
        ]);

        $this->appendMenuPromptsToGather($gather);

        // If caller enters nothing within the Gather timeout, redirect to retry
        $timeoutUrl = '/twilio/menu?retry=' . ($retryCount + 1) . '&timeout=1';
        $response->redirect($timeoutUrl, ['method' => 'POST']);
    }

    /**
     * Dispatch an SMS job synchronously during tests or asynchronously after response in production.
     */
    protected function dispatchSmsJob(
        string $toNumber,
        string $message,
        ?int $callLogId = null,
        string $type = 'option_sms',
        ?string $optionDigit = null,
        ?string $fromNumber = null
    ): void {
        if (app()->runningInConsole() || app()->runningUnitTests()) {
            SendIvrSmsJob::dispatchSync($toNumber, $message, $callLogId, $type, $optionDigit, $fromNumber);
        } else {
            SendIvrSmsJob::dispatch($toNumber, $message, $callLogId, $type, $optionDigit, $fromNumber)->afterResponse();
        }
    }
}
