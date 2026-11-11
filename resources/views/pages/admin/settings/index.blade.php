@extends('layouts.app')

@section('content')
    <div class="max-w-4xl space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                    IVR System Configuration & Settings
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Configure environment credentials, call limits, keypad timeouts, SMS dispatching, and text-to-speech fallback prompts.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    Hot-Reload Telephony Config
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                <div class="flex items-center gap-2 mb-1.5 font-bold">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Please fix the following issues:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs font-normal">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.ivr.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Twilio API & Environment Credentials -->
            <div x-data="{ showAuthToken: false }" class="rounded-2xl border border-brand-200/80 bg-brand-50/20 p-6 dark:border-brand-900/40 dark:bg-brand-950/10 shadow-xs">
                <div class="border-b border-brand-100 dark:border-brand-900/30 pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-brand-500"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Twilio API & Telephony Credentials</h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Manage your Twilio account API keys, telephone number, and application URL settings directly from this dashboard.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Twilio Account SID -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Twilio Account SID (TWILIO_ACCOUNT_SID)
                        </label>
                        <input type="text" id="twilio_account_sid" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings['twilio_account_sid']) }}" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full font-mono rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Found in Twilio Console Dashboard under "Account Info".</span>
                    </div>

                    <!-- Twilio Auth Token -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Twilio Auth Token (TWILIO_AUTH_TOKEN)
                            </label>
                            <button type="button" @click="showAuthToken = !showAuthToken" class="text-[11px] font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                <span x-text="showAuthToken ? 'Hide Secret' : 'Show Secret'"></span>
                            </button>
                        </div>
                        <div class="relative">
                            <input :type="showAuthToken ? 'text' : 'password'" id="twilio_auth_token" name="twilio_auth_token" value="{{ old('twilio_auth_token', $settings['twilio_auth_token']) }}" placeholder="Enter Twilio Auth Token"
                                class="w-full font-mono rounded-xl border border-gray-300 bg-white px-4 py-2.5 pe-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <button type="button" @click="showAuthToken = !showAuthToken" class="absolute top-1/2 end-3 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg x-show="!showAuthToken" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showAuthToken" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-[11px] text-gray-400 mt-1 block">Your primary account authorization secret.</span>
                    </div>

                    <!-- Twilio Phone Number -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Twilio Phone Number (TWILIO_PHONE_NUMBER)
                        </label>
                        <input type="text" id="twilio_phone_number" name="twilio_phone_number" value="{{ old('twilio_phone_number', $settings['twilio_phone_number']) }}" placeholder="+18325512407"
                            class="w-full font-mono rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Twilio assigned phone number in E.164 format (e.g. +1234567890).</span>
                    </div>

                    <!-- Application Public Base URL -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Application Base URL (APP_URL)
                        </label>
                        <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url']) }}" placeholder="http://localhost:8000"
                            class="w-full font-mono rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Used by Twilio webhook URLs and public audio streaming.</span>
                    </div>
                </div>

                <!-- Webhook Security Toggle -->
                <div class="mt-5 pt-4 border-t border-brand-100/70 dark:border-brand-900/30 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white block">Twilio Webhook Signature Validation</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Enforces X-Twilio-Signature verification to ensure all incoming calls originate from Twilio.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="twilio_webhook_validation" value="1" {{ $settings['twilio_webhook_validation'] ? 'checked' : '' }} class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    </label>
                </div>
            </div>

            <!-- Section 2: Telephony Controls & Limits -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-brand-500"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Call Flow Controls & Retries</h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Manage caller timeouts, retry thresholds, and loop prevention.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Max Retries Before Disconnect <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="max_retries" value="{{ old('max_retries', $settings['max_retries']) }}" min="1" max="10" required
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Max invalid inputs or timeouts allowed before playing Goodbye and hanging up.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Keypad Gather Timeout (seconds) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="gather_timeout" value="{{ old('gather_timeout', $settings['gather_timeout']) }}" min="2" max="30" required
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Twilio waits this many seconds for caller to press a digit before prompting.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Return to Main Menu Keypad Digit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="return_to_menu_digit" value="{{ old('return_to_menu_digit', $settings['return_to_menu_digit']) }}" maxlength="2" required
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm font-mono font-bold text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Digit callers press to go back to the main options menu (default: 9).</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Return to Main Menu Voice Prompt <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="return_to_menu_prompt_text" value="{{ old('return_to_menu_prompt_text', $settings['return_to_menu_prompt_text']) }}" required
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        <span class="text-[11px] text-gray-400 mt-1 block">Spoken after option audio playback completes.</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Twilio SMS Integration -->
            <div class="rounded-2xl border border-purple-200/80 bg-purple-50/20 p-6 dark:border-purple-900/40 dark:bg-purple-950/10 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-purple-100 dark:border-purple-900/30 pb-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-purple-500"></span>
                            <h2 class="text-base font-bold text-purple-900 dark:text-purple-300">Twilio SMS Dispatch Feature</h2>
                        </div>
                        <p class="text-xs text-purple-700/70 dark:text-purple-300/60 mt-0.5">
                            Automatically text callers recruitment portals or information links upon keypad request.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sms_enabled" value="1" {{ $settings['sms_enabled'] ? 'checked' : '' }} class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                        <span class="ms-3 text-sm font-semibold text-gray-800 dark:text-gray-200">Enable SMS Dispatch</span>
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Default SMS Message Content <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="sms_message" rows="3" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">{{ old('sms_message', $settings['sms_message']) }}</textarea>
                        <span class="text-[11px] text-gray-400 mt-1 block">Sent to the caller's mobile number via Twilio Messaging API.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Voice Confirmation Message (Spoken to caller) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="sms_confirmation_fallback_text" value="{{ old('sms_confirmation_fallback_text', $settings['sms_confirmation_fallback_text']) }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                    </div>
                </div>
            </div>

            <!-- Section 4: Speech Fallback Prompts (Amazon Polly Joanna) -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-brand-500"></span>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Text-to-Speech Fallback Messages</h2>
                        </div>
                        <span class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/40 px-2.5 py-0.5 rounded-full">
                            Amazon Polly Joanna
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Spoken automatically whenever an audio recording is missing or not uploaded.
                    </p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Opening Welcome / Introduction Fallback Speech
                        </label>
                        <textarea name="welcome_fallback_text" rows="2"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition">{{ old('welcome_fallback_text', $settings['welcome_fallback_text']) }}</textarea>
                        <span class="text-[11px] text-gray-400 mt-1 block">Spoken when caller first dials in if no opening welcome audio recording is uploaded.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Main Presentation Fallback Speech (Optional)
                        </label>
                        <textarea name="main_presentation_fallback_text" rows="3"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition">{{ old('main_presentation_fallback_text', $settings['main_presentation_fallback_text']) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Main Menu Speech Instructions (Optional)
                        </label>
                        <textarea name="menu_fallback_text" rows="2" placeholder="Leave empty to dynamically speak active options (e.g. 'Press 1 for Learn FlowCore...')"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition">{{ old('menu_fallback_text', $settings['menu_fallback_text']) }}</textarea>
                        <span class="text-[11px] text-gray-400 mt-1 block">Plays immediately after the opening welcome or when returning to main menu (key 0). Leaving this blank auto-builds menu instructions from active options.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Invalid Selection Voice Prompt
                            </label>
                            <input type="text" name="invalid_fallback_text" value="{{ old('invalid_fallback_text', $settings['invalid_fallback_text']) }}"
                                class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                No-Input Timeout Voice Prompt
                            </label>
                            <input type="text" name="no_input_fallback_text" value="{{ old('no_input_fallback_text', $settings['no_input_fallback_text']) }}"
                                class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Goodbye & Disconnect Voice Prompt
                        </label>
                        <input type="text" name="goodbye_fallback_text" value="{{ old('goodbye_fallback_text', $settings['goodbye_fallback_text']) }}"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-semibold text-white bg-brand-500 rounded-xl hover:bg-brand-600 shadow-sm transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save System Settings & Credentials
                </button>
            </div>
        </form>
    </div>
@endsection
