<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AudioUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'audio_file' => ['required', 'file', 'mimes:mp3,wav,m4a', 'max:20480'],
            'target_type' => [
                'required',
                Rule::in([
                    'welcome',
                    'main_presentation',
                    'menu_prompt',
                    'invalid_input',
                    'no_input',
                    'goodbye',
                    'option',
                ]),
            ],
            'option_id' => [
                'nullable',
                Rule::requiredIf($this->input('target_type') === 'option'),
                'exists:ivr_options,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'audio_file.required' => 'Please select an audio file to upload.',
            'audio_file.mimes' => 'The audio file must be an MP3, WAV, or M4A file.',
            'audio_file.max' => 'The audio file size may not exceed 20MB.',
            'option_id.required_if' => 'An IVR option must be selected when target type is option.',
        ];
    }
}
