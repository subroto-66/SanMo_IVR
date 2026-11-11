<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IvrOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $optionId = $this->route('option')?->id ?? $this->route('id');
        $isActive = filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        return [
            'digit' => [
                'required',
                'string',
                'max:5',
                // Unique among active options if this option is being set to active
                Rule::unique('ivr_options', 'digit')
                    ->where(fn ($query) => $query->where('is_active', true))
                    ->ignore($optionId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'action_type' => ['required', Rule::in(['audio', 'sms', 'menu', 'goodbye', 'contact_request'])],
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_message' => ['nullable', 'string', 'max:1600'],
            'fallback_text' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:20480'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'digit.unique' => 'The selected keypad digit is already assigned to another active IVR option.',
            'audio_file.mimes' => 'The audio file must be an MP3, WAV, or M4A file.',
            'audio_file.max' => 'The audio file size may not exceed 20MB.',
        ];
    }
}
