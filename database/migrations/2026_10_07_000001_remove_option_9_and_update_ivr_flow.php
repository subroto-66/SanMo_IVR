<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Delete Option 9 from IVR options
        DB::table('ivr_options')->where('digit', '9')->delete();

        // 2. Remove repeat_menu_digit setting
        DB::table('ivr_settings')->where('key', 'repeat_menu_digit')->delete();

        // 3. Update return_to_menu_prompt_text to remove reference to 9
        DB::table('ivr_settings')->where('key', 'return_to_menu_prompt_text')->update([
            'value' => 'To return to the main menu, press 0. To end your call, press pound.',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ivr_options')->insert([
            'digit' => '9',
            'title' => 'Repeat Current Menu/Options',
            'description' => 'Replays the current menu and options.',
            'action_type' => 'menu',
            'sms_enabled' => false,
            'sms_message' => null,
            'fallback_text' => 'Replaying the menu options.',
            'is_active' => true,
            'sort_order' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ivr_settings')->insert([
            'key' => 'repeat_menu_digit',
            'value' => '9',
            'type' => 'text',
            'group' => 'general',
            'label' => 'Repeat Menu Keypad Digit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
