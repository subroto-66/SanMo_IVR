<?php

use App\Models\IvrOption;
use App\Models\IvrSetting;
use App\Services\AudioStorageService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safely resets any database audio path pointing to non-existent files on server storage,
     * ensuring all IVR calls use Polly text-to-speech fallback instead of fatal 404 application errors.
     */
    public function up(): void
    {
        $storage = app(AudioStorageService::class);

        // 1. Clean missing audio records from IVR Keypad Options (1-5, etc.)
        $options = IvrOption::whereNotNull('audio_path')->orWhereNotNull('audio_url')->get();
        foreach ($options as $option) {
            $pathMissing = !empty($option->audio_path) && !$storage->exists($option->audio_path);
            $urlMissing = !empty($option->audio_url) && !$storage->exists($option->audio_url);

            if ($pathMissing || $urlMissing) {
                $option->update([
                    'audio_path' => null,
                    'audio_url' => null,
                ]);
            }
        }

        // 2. Clean missing audio records from System IVR Settings
        $audioSettings = [
            'welcome_audio_path',
            'main_presentation_audio_path',
            'menu_prompt_audio_path',
            'invalid_input_audio_path',
            'no_input_audio_path',
            'goodbye_audio_path',
        ];

        foreach ($audioSettings as $key) {
            $path = IvrSetting::get($key);
            if (!empty($path) && !$storage->exists($path)) {
                IvrSetting::set($key, null);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for cleaning up missing file records
    }
};
