<?php

use App\Models\IvrOption;
use App\Models\IvrSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Core Flow Settings
        $settings = [
            'welcome_fallback_text' => 'Welcome to the FlowCore recruitment line.',
            'main_presentation_fallback_text' => 'Thank you for your interest in FlowCore. FlowCore provides innovative opportunities and cutting edge programs.',
            'menu_fallback_text' => '',
            'return_to_menu_digit' => '0',
            'repeat_menu_digit' => '9',
            'end_call_digit' => '#',
            'return_to_menu_prompt_text' => 'To repeat this menu, press 9. To return to the main menu, press 0. To end your call, press pound.',
            'sms_message' => 'Thank you for your interest in FlowCore! Learn more about FlowCore at: https://DiscoverFlowCore.com/learn',
            'sms_confirmation_fallback_text' => 'Thank you. We have sent you a text message with details and your link.',
        ];

        foreach ($settings as $key => $value) {
            IvrSetting::set($key, $value);
        }

        // 2. FlowCore Recruitment Keypad Options
        $flowCoreOptions = [
            [
                'digit' => '1',
                'title' => 'Learn About FlowCore',
                'description' => 'Overview of FlowCore with information link sent via SMS.',
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
                'action_type' => 'contact_request',
                'sms_enabled' => true,
                'sms_message' => 'Thank you for reaching out to FlowCore. A representative has received your contact request and will follow up with you shortly.',
                'fallback_text' => 'Thank you. Your request for personal contact has been received. A FlowCore representative will follow up with you shortly.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'digit' => '9',
                'title' => 'Repeat Current Menu/Options',
                'description' => 'Replays the current menu and options.',
                'action_type' => 'menu',
                'sms_enabled' => false,
                'sms_message' => null,
                'fallback_text' => 'Replaying the menu options.',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'digit' => '0',
                'title' => 'Return to Main Menu',
                'description' => 'Returns caller to the main FlowCore recruitment menu.',
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
                'action_type' => 'goodbye',
                'sms_enabled' => false,
                'sms_message' => null,
                'fallback_text' => 'Thank you for calling the FlowCore recruitment line. Goodbye.',
                'is_active' => true,
                'sort_order' => 11,
            ],
        ];

        foreach ($flowCoreOptions as $opt) {
            IvrOption::updateOrCreate(
                ['digit' => $opt['digit']],
                $opt
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or revert if desired
    }
};
