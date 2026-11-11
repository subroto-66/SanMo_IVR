<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AudioUploadRequest;
use App\Models\IvrOption;
use App\Models\IvrSetting;
use App\Services\AudioStorageService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AudioManagementController extends Controller
{
    protected AudioStorageService $audioStorageService;

    public function __construct(AudioStorageService $audioStorageService)
    {
        $this->audioStorageService = $audioStorageService;
    }

    /**
     * Display the centralized Audio Management interface.
     */
    public function index(): View
    {
        // 1. System-level audio entries
        $systemAudioSlots = [
            [
                'key' => 'welcome_audio_path',
                'target_type' => 'welcome',
                'title' => 'Opening Welcome / Introduction',
                'description' => 'Plays when the call first begins before the main menu options.',
                'path' => IvrSetting::get('welcome_audio_path'),
                'fallback' => IvrSetting::get('welcome_fallback_text', 'Welcome to the FlowCore recruitment line.'),
            ],
            [
                'key' => 'menu_prompt_audio_path',
                'target_type' => 'menu_prompt',
                'title' => 'Main Menu',
                'description' => 'Plays immediately after the opening welcome when someone calls, and plays directly when callers press 0 to return to the main menu. Leave empty to auto-generate from active keypad options.',
                'path' => IvrSetting::get('menu_prompt_audio_path'),
                'fallback' => IvrSetting::get('menu_fallback_text'),
            ],
            [
                'key' => 'invalid_input_audio_path',
                'target_type' => 'invalid_input',
                'title' => 'Invalid Selection Audio',
                'description' => 'Plays when caller presses an unrecognized key.',
                'path' => IvrSetting::get('invalid_input_audio_path'),
                'fallback' => IvrSetting::get('invalid_fallback_text'),
            ],
            [
                'key' => 'no_input_audio_path',
                'target_type' => 'no_input',
                'title' => 'No Response / Timeout Audio',
                'description' => 'Plays when caller does not press any key before timeout.',
                'path' => IvrSetting::get('no_input_audio_path'),
                'fallback' => IvrSetting::get('no_input_fallback_text'),
            ],
            [
                'key' => 'goodbye_audio_path',
                'target_type' => 'goodbye',
                'title' => 'Goodbye / Call Termination Audio',
                'description' => 'Plays before hanging up after max retries or goodbye action.',
                'path' => IvrSetting::get('goodbye_audio_path'),
                'fallback' => IvrSetting::get('goodbye_fallback_text'),
            ],
        ];

        // Enrich system slots with storage metadata
        foreach ($systemAudioSlots as &$slot) {
            $slot['file_exists'] = !empty($slot['path']) && $this->audioStorageService->exists($slot['path']);
            $slot['url'] = !empty($slot['path']) ? $this->audioStorageService->getUrl($slot['path']) : null;
            $slot['meta'] = !empty($slot['path']) ? $this->audioStorageService->getMetadata($slot['path']) : null;
        }
        unset($slot);

        // 2. Option audio entries
        $options = IvrOption::ordered()->get();
        $optionAudioSlots = [];

        foreach ($options as $option) {
            $fileExists = !empty($option->audio_path) && $this->audioStorageService->exists($option->audio_path);
            $meta = !empty($option->audio_path) ? $this->audioStorageService->getMetadata($option->audio_path) : null;
            $optionAudioSlots[] = [
                'option' => $option,
                'target_type' => 'option',
                'title' => "Option {$option->digit}: {$option->title}",
                'path' => $option->audio_path,
                'url' => $option->resolved_audio_url,
                'file_exists' => $fileExists,
                'meta' => $meta,
                'fallback' => $option->fallback_text,
            ];
        }

        return view('pages.admin.audio.index', [
            'title' => 'IVR Audio Management',
            'systemAudioSlots' => $systemAudioSlots,
            'optionAudioSlots' => $optionAudioSlots,
        ]);
    }

    /**
     * Upload or replace an audio file.
     */
    public function upload(AudioUploadRequest $request): RedirectResponse
    {
        $targetType = $request->input('target_type');
        $file = $request->file('audio_file');

        try {
            if ($targetType === 'option') {
                $option = IvrOption::findOrFail($request->input('option_id'));
                $oldPaths = array_filter([$option->audio_path, $option->audio_url]);
                $upload = $this->audioStorageService->replace($file, $oldPaths, 'audio/options');

                $option->update([
                    'audio_path' => $upload['path'],
                    'audio_url' => $upload['url'],
                ]);

                return back()->with('success', "Audio for Option {$option->digit} uploaded successfully.");
            }

            // System audio slot
            $keyMap = [
                'welcome' => 'welcome_audio_path',
                'main_presentation' => 'main_presentation_audio_path',
                'menu_prompt' => 'menu_prompt_audio_path',
                'invalid_input' => 'invalid_input_audio_path',
                'no_input' => 'no_input_audio_path',
                'goodbye' => 'goodbye_audio_path',
            ];

            $settingKey = $keyMap[$targetType] ?? null;
            if (!$settingKey) {
                return back()->with('error', 'Invalid audio target type.');
            }

            $oldPath = IvrSetting::get($settingKey);
            $upload = $this->audioStorageService->replace($file, $oldPath, 'audio/system');

            IvrSetting::set($settingKey, $upload['path'], [
                'type' => 'audio',
                'group' => 'audio',
            ]);

            return back()->with('success', 'Audio file uploaded and updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to upload audio: ' . $e->getMessage());
        }
    }

    /**
     * Remove an audio file and revert to fallback text.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $targetType = $request->input('target_type');

        try {
            if ($targetType === 'option') {
                $option = IvrOption::findOrFail($request->input('option_id'));
                $oldPaths = array_filter([$option->audio_path, $option->audio_url]);
                if (!empty($oldPaths)) {
                    $this->audioStorageService->delete($oldPaths);
                }
                $option->update([
                    'audio_path' => null,
                    'audio_url' => null,
                ]);

                return back()->with('success', "Audio for Option {$option->digit} removed. Reverted to fallback.");
            }

            $keyMap = [
                'welcome' => 'welcome_audio_path',
                'main_presentation' => 'main_presentation_audio_path',
                'menu_prompt' => 'menu_prompt_audio_path',
                'invalid_input' => 'invalid_input_audio_path',
                'no_input' => 'no_input_audio_path',
                'goodbye' => 'goodbye_audio_path',
            ];

            $settingKey = $keyMap[$targetType] ?? null;
            if ($settingKey) {
                $oldPath = IvrSetting::get($settingKey);
                if (!empty($oldPath)) {
                    $this->audioStorageService->delete($oldPath);
                }
                IvrSetting::set($settingKey, null);
                return back()->with('success', 'Audio removed. Reverted to text-to-speech fallback.');
            }

            return back()->with('error', 'Invalid target type.');
        } catch (Exception $e) {
            return back()->with('error', 'Error removing audio: ' . $e->getMessage());
        }
    }

    /**
     * Stream an audio file directly with HTTP Range (206 Partial Content) support.
     */
    public function stream(string $filename)
    {
        $cleanPath = ltrim(str_replace('\\', '/', $filename), '/');

        // Prevent directory traversal
        if (str_contains($cleanPath, '..') || str_contains($cleanPath, "\0")) {
            abort(403, 'Invalid audio file request.');
        }

        // Possible storage locations
        $candidates = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            storage_path('app/' . $cleanPath),
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            Log::warning('Audio file requested but missing from server storage, serving silent audio fallback', [
                'filename' => $filename,
                'clean_path' => $cleanPath,
            ]);

            // Minimal valid 1-frame MPEG-1 Layer 3 silent audio stream (187 bytes).
            // Prevents Twilio voice calls from terminating with "An application error has occurred" on 404.
            $silentMp3 = base64_decode('//uQZAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAACcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==');

            return response($silentMp3, 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Length' => strlen($silentMp3),
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeMap = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
        ];
        $mimeType = $mimeMap[$extension] ?? (mime_content_type($filePath) ?: 'audio/mpeg');

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
        ]);
    }
}

