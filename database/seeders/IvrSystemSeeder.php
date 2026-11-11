<?php

namespace Database\Seeders;

use App\Models\IvrOption;
use App\Models\IvrSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class IvrSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or ensure Default Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Default IVR Settings
        $settings = [
            // Audio paths (initially null until uploaded by client)
            ['key' => 'welcome_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'Opening Welcome / Introduction Audio'],
            ['key' => 'main_presentation_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'Main Presentation Audio'],
            ['key' => 'menu_prompt_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'Main Menu Audio'],
            ['key' => 'invalid_input_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'Invalid Selection Audio'],
            ['key' => 'no_input_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'No Response Audio'],
            ['key' => 'goodbye_audio_path', 'value' => null, 'type' => 'audio', 'group' => 'audio', 'label' => 'Goodbye Audio'],

            // Text-to-speech fallbacks
            ['key' => 'welcome_fallback_text', 'value' => 'Welcome to the FlowCore recruitment line.', 'type' => 'textarea', 'group' => 'prompts', 'label' => 'Welcome Fallback Text'],
            ['key' => 'main_presentation_fallback_text', 'value' => 'Thank you for your interest in FlowCore. FlowCore provides innovative opportunities and cutting edge programs.', 'type' => 'textarea', 'group' => 'prompts', 'label' => 'Main Presentation Fallback Text'],
            ['key' => 'menu_fallback_text', 'value' => '', 'type' => 'textarea', 'group' => 'prompts', 'label' => 'Menu Fallback Text (empty auto-generates options)'],
            ['key' => 'invalid_fallback_text', 'value' => 'Sorry, that is not a valid selection. Please try again.', 'type' => 'text', 'group' => 'prompts', 'label' => 'Invalid Selection Fallback Text'],
            ['key' => 'no_input_fallback_text', 'value' => 'We did not receive a selection. Please choose an option.', 'type' => 'text', 'group' => 'prompts', 'label' => 'No Response Fallback Text'],
            ['key' => 'goodbye_fallback_text', 'value' => 'Thank you for calling the FlowCore recruitment line. Have a great day. Goodbye.', 'type' => 'text', 'group' => 'prompts', 'label' => 'Goodbye Fallback Text'],

            // Core flow settings
            ['key' => 'max_retries', 'value' => '3', 'type' => 'number', 'group' => 'general', 'label' => 'Max Retries Before Disconnect'],
            ['key' => 'gather_timeout', 'value' => '5', 'type' => 'number', 'group' => 'general', 'label' => 'Keypad Input Timeout (seconds)'],
            ['key' => 'return_to_menu_digit', 'value' => '0', 'type' => 'text', 'group' => 'general', 'label' => 'Return to Menu Keypad Digit'],
            ['key' => 'end_call_digit', 'value' => '#', 'type' => 'text', 'group' => 'general', 'label' => 'End Call Keypad Digit'],
            ['key' => 'return_to_menu_prompt_text', 'value' => 'To return to the main menu, press 0. To end your call, press pound.', 'type' => 'text', 'group' => 'prompts', 'label' => 'Return to Menu Prompt'],

            // SMS Configuration
            ['key' => 'sms_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'sms', 'label' => 'Enable SMS Information Feature'],
            ['key' => 'sms_message', 'value' => 'Thank you for your interest in FlowCore! Learn more about FlowCore at: https://DiscoverFlowCore.com/learn', 'type' => 'textarea', 'group' => 'sms', 'label' => 'Default SMS Information Message'],
            ['key' => 'sms_confirmation_fallback_text', 'value' => 'Thank you. We have sent you a text message with details and your link.', 'type' => 'text', 'group' => 'sms', 'label' => 'SMS Confirmation Voice Message'],
        ];

        foreach ($settings as $setting) {
            IvrSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // 3. FlowCore Recruitment IVR Options
        $flowCoreOptions = [
            [
                'digit' => '1',
                'title' => 'Learn About FlowCore',
                'description' => 'Overview of FlowCore with information link sent via SMS.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'sms',
                'sms_enabled' => true,
                'sms_message' => 'Thank you for your interest in FlowCore! Learn more about FlowCore at: https://DiscoverFlowCore.com/learn',
                'fallback_text' => 'Thank you for choosing Learn About FlowCore. We have sent a text message with details and a link directly to your mobile phone.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'digit' => '2',
                'title' => 'Receive the FlowCore Presentation',
                'description' => 'Official FlowCore presentation link delivered via SMS.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'sms',
                'sms_enabled' => true,
                'sms_message' => 'Here is your link to the official FlowCore presentation: https://DiscoverFlowCore.com/presentation',
                'fallback_text' => 'Thank you. We have sent the link to the official FlowCore presentation directly to your phone via text message.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'digit' => '3',
                'title' => 'Participation & Compensation',
                'description' => 'Information regarding participation and compensation opportunities sent via SMS.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'sms',
                'sms_enabled' => true,
                'sms_message' => 'Discover FlowCore participation and compensation opportunities here: https://DiscoverFlowCore.com/opportunity',
                'fallback_text' => 'Thank you. We are texting you information regarding FlowCore participation and compensation opportunities now.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'digit' => '4',
                'title' => 'Recruitment Process & Next Steps',
                'description' => 'Recruitment intake and onboarding next-step instructions sent via SMS.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'sms',
                'sms_enabled' => true,
                'sms_message' => 'Review the FlowCore recruitment process and next steps here: https://DiscoverFlowCore.com/next',
                'fallback_text' => 'Thank you. Information on our recruitment process and next steps has been sent to your phone via text message.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'digit' => '5',
                'title' => 'Request Personal Contact',
                'description' => 'Logs contact request for a FlowCore representative to follow up with caller.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'contact_request',
                'sms_enabled' => true,
                'sms_message' => 'Thank you for reaching out to FlowCore. A representative has received your contact request and will follow up with you shortly.',
                'fallback_text' => 'Thank you. Your request for personal contact has been received. A FlowCore representative will follow up with you shortly.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'digit' => '0',
                'title' => 'Return to Main Menu',
                'description' => 'Returns caller to the main FlowCore recruitment menu.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'menu',
                'sms_enabled' => false,
                'sms_message' => null,
                'fallback_text' => 'Returning to the main FlowCore recruitment menu.',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'digit' => '#',
                'title' => 'End Call',
                'description' => 'Gracefully terminates the call with goodbye audio.',
                'audio_path' => null,
                'audio_url' => null,
                'action_type' => 'goodbye',
                'sms_enabled' => false,
                'sms_message' => null,
                'fallback_text' => 'Thank you for calling the FlowCore recruitment line. Goodbye.',
                'is_active' => true,
                'sort_order' => 11,
            ],
        ];

        foreach ($flowCoreOptions as $optData) {
            IvrOption::updateOrCreate(
                ['digit' => $optData['digit']],
                $optData
            );
        }
    }
}
