<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| IVR Audio Diagnostic & Cleanup Commands
|--------------------------------------------------------------------------
*/

Artisan::command('ivr:verify-audio', function (\App\Services\AudioStorageService $storage) {
    $this->info('--- Verifying System Audio Slots ---');
    $keys = [
        'welcome_audio_path',
        'main_presentation_audio_path',
        'menu_prompt_audio_path',
        'invalid_input_audio_path',
        'no_input_audio_path',
        'goodbye_audio_path',
    ];

    foreach ($keys as $key) {
        $path = \App\Models\IvrSetting::get($key);
        if (empty($path)) {
            $this->line(" [SETTING] {$key}: (none configured) -> TTS Fallback Active");
        } else {
            $exists = $storage->exists($path);
            if ($exists) {
                $this->info(" [SETTING] {$key}: {$path} -> [OK: File Found]");
            } else {
                $this->error(" [SETTING] {$key}: {$path} -> [MISSING: File not found on server]");
            }
        }
    }

    $this->info("\n--- Verifying Keypad Option Audio Slots ---");
    $options = \App\Models\IvrOption::ordered()->get();
    foreach ($options as $opt) {
        if (empty($opt->audio_path) && empty($opt->audio_url)) {
            $this->line(" [OPTION {$opt->digit}] {$opt->title}: (none configured) -> TTS Fallback Active");
        } else {
            $path = $opt->audio_path ?: $opt->audio_url;
            $exists = $storage->exists($path);
            if ($exists) {
                $this->info(" [OPTION {$opt->digit}] {$opt->title}: {$path} -> [OK: File Found]");
            } else {
                $this->error(" [OPTION {$opt->digit}] {$opt->title}: {$path} -> [MISSING: File not found on server]");
            }
        }
    }
})->purpose('Verify that all configured audio files exist on the server storage');

Artisan::command('ivr:clean-audio {--orphans : Also delete unreferenced orphan audio files from storage disk}', function (\App\Services\AudioStorageService $storage) {
    $this->info('Scanning and cleaning missing audio records from database...');

    $cleanedOptions = 0;
    $options = \App\Models\IvrOption::whereNotNull('audio_path')->orWhereNotNull('audio_url')->get();
    foreach ($options as $opt) {
        $pathMissing = !empty($opt->audio_path) && !$storage->exists($opt->audio_path);
        $urlMissing = !empty($opt->audio_url) && !$storage->exists($opt->audio_url);

        if ($pathMissing || $urlMissing) {
            $this->warn("Option {$opt->digit} audio was missing. Resetting to null (reverting to TTS).");
            $opt->update(['audio_path' => null, 'audio_url' => null]);
            $cleanedOptions++;
        }
    }

    $cleanedSettings = 0;
    $keys = [
        'welcome_audio_path',
        'main_presentation_audio_path',
        'menu_prompt_audio_path',
        'invalid_input_audio_path',
        'no_input_audio_path',
        'goodbye_audio_path',
    ];

    foreach ($keys as $key) {
        $path = \App\Models\IvrSetting::get($key);
        if (!empty($path) && !$storage->exists($path)) {
            $this->warn("Setting {$key} audio was missing ({$path}). Resetting to null (reverting to TTS).");
            \App\Models\IvrSetting::set($key, null);
            $cleanedSettings++;
        }
    }

    $this->info("Database audit complete: Cleaned {$cleanedOptions} option(s) and {$cleanedSettings} system setting(s).");

    // Optional orphan file scan & cleanup
    if ($this->option('orphans')) {
        $this->info("\nScanning storage for unreferenced orphan audio files...");
        $activeFiles = [];

        foreach (\App\Models\IvrSetting::whereIn('key', $keys)->pluck('value') as $val) {
            if ($val) {
                $clean = $storage->cleanRelativePath($val);
                if ($clean) $activeFiles[$clean] = true;
            }
        }

        foreach (\App\Models\IvrOption::all() as $opt) {
            foreach ([$opt->audio_path, $opt->audio_url] as $val) {
                if ($val) {
                    $clean = $storage->cleanRelativePath($val);
                    if ($clean) $activeFiles[$clean] = true;
                }
            }
        }

        $directories = [
            storage_path('app/public/audio/options'),
            storage_path('app/public/audio/system'),
            public_path('audio/options'),
            public_path('audio/system'),
        ];

        $deletedOrphans = 0;
        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;
            $files = glob($dir . '/*.*');
            foreach ($files as $filePath) {
                $relSystem = 'audio/system/' . basename($filePath);
                $relOption = 'audio/options/' . basename($filePath);

                if (!isset($activeFiles[$relSystem]) && !isset($activeFiles[$relOption])) {
                    $this->warn("Deleting unreferenced orphan file: " . basename($filePath));
                    @unlink($filePath);
                    $deletedOrphans++;
                }
            }
        }
        $this->info("Orphan cleanup finished: Removed {$deletedOrphans} unreferenced file(s).");
    }
})->purpose('Reset database records pointing to missing audio files and optionally clean orphan files');
