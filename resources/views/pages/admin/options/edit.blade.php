@extends('layouts.app')

@section('content')
    <div class="max-w-4xl space-y-6" x-data="{
        digit: '{{ old('digit', $option->digit) }}',
        actionType: '{{ old('action_type', $option->action_type) }}',
        selectedFileName: '',
        selectedFileSize: '',
        selectedFileAudioUrl: null,
        isDragging: false,
        selectDigit(val) {
            this.digit = val;
        },
        handleFileSelect(event) {
            const file = event.target?.files?.[0];
            if (!file) return;
            this.selectedFileName = file.name;
            this.selectedFileSize = (file.size / 1024).toFixed(1) + ' KB';
            if (this.selectedFileAudioUrl) {
                URL.revokeObjectURL(this.selectedFileAudioUrl);
            }
            this.selectedFileAudioUrl = URL.createObjectURL(file);
        },
        resetFile() {
            this.selectedFileName = '';
            this.selectedFileSize = '';
            if (this.selectedFileAudioUrl) {
                URL.revokeObjectURL(this.selectedFileAudioUrl);
                this.selectedFileAudioUrl = null;
            }
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.ivr.options.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Keypad Options
                </a>
                <div class="flex items-center gap-3 mt-1.5">
                    <div class="inline-flex flex-col items-center justify-center size-9 rounded-xl bg-gray-900 text-white font-mono font-bold text-base shadow-sm ring-1 ring-black/10 dark:bg-gray-100 dark:text-gray-900">
                        <span>{{ $option->digit }}</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                            Edit Keypad Option: {{ $option->title }}
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Modify behavior, audio presentation, or SMS message for callers dialing digit {{ $option->digit }}.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                <div class="flex items-center gap-2">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                <div class="flex items-center gap-2 mb-1.5 font-bold">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Please correct the following:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs font-normal">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.ivr.options.update', $option) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Keypad Digit & Information -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">1. Dialpad Digit Assignment</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Keypad digit assigned to this option.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                    <!-- Digit Input & Quick Select -->
                    <div class="md:col-span-4 space-y-3">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Keypad Key <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="text" name="digit" x-model="digit" required maxlength="5"
                                class="w-20 text-center font-mono text-xl font-bold rounded-xl border border-gray-300 bg-gray-50/50 py-3 text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400">Quick select:</span>
                                <div class="flex flex-wrap gap-1 max-w-[140px]">
                                    @foreach (['1','2','3','4','5','6','7','8','9','*','0','#'] as $key)
                                        <button type="button" @click="selectDigit('{{ $key }}')"
                                            class="size-7 rounded-lg border border-gray-200 bg-white font-mono text-xs font-bold text-gray-700 hover:border-brand-400 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-brand-950 transition">
                                            {{ $key }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400">Dialpad key pressed by caller.</p>
                    </div>

                    <!-- Title & Internal Description -->
                    <div class="md:col-span-8 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Menu Title / Label <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $option->title) }}" required
                                class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                            <p class="text-[11px] text-gray-400 mt-1">Spoken in the main voice prompt and displayed across admin reports.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Display Sort Order
                                </label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', $option->sort_order) }}" min="0" max="999"
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Internal Notes (Optional)
                                </label>
                                <input type="text" name="description" value="{{ old('description', $option->description) }}" placeholder="e.g. Current hiring cycle"
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Action Type Visual Selector -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">2. Action on Keypad Press</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Determine how the call proceeds when caller dials this key.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Audio Option -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                        :class="actionType === 'audio' ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-950/20 ring-1 ring-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
                        <input type="radio" name="action_type" value="audio" x-model="actionType" class="sr-only" />
                        <div class="flex items-center justify-between mb-2">
                            <span class="size-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center dark:bg-blue-950 dark:text-blue-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                </svg>
                            </span>
                            <span class="size-4 rounded-full border-2 flex items-center justify-center"
                                :class="actionType === 'audio' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                <span class="size-1.5 rounded-full bg-white" x-show="actionType === 'audio'"></span>
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Audio Playback</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Plays prerecorded audio presentation.</span>
                    </label>

                    <!-- SMS Option -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                        :class="actionType === 'sms' ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-950/20 ring-1 ring-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
                        <input type="radio" name="action_type" value="sms" x-model="actionType" class="sr-only" />
                        <div class="flex items-center justify-between mb-2">
                            <span class="size-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center dark:bg-purple-950 dark:text-purple-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </span>
                            <span class="size-4 rounded-full border-2 flex items-center justify-center"
                                :class="actionType === 'sms' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                <span class="size-1.5 rounded-full bg-white" x-show="actionType === 'sms'"></span>
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Send SMS Link</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Confirmation audio & SMS link.</span>
                    </label>

                    <!-- Contact Request Option -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                        :class="actionType === 'contact_request' ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-950/20 ring-1 ring-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
                        <input type="radio" name="action_type" value="contact_request" x-model="actionType" class="sr-only" />
                        <div class="flex items-center justify-between mb-2">
                            <span class="size-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center dark:bg-amber-950 dark:text-amber-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a11.042 11.042 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <span class="size-4 rounded-full border-2 flex items-center justify-center"
                                :class="actionType === 'contact_request' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                <span class="size-1.5 rounded-full bg-white" x-show="actionType === 'contact_request'"></span>
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Contact Request</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Logs personal callback request.</span>
                    </label>

                    <!-- Menu Loop Option -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                        :class="actionType === 'menu' ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-950/20 ring-1 ring-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
                        <input type="radio" name="action_type" value="menu" x-model="actionType" class="sr-only" />
                        <div class="flex items-center justify-between mb-2">
                            <span class="size-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center dark:bg-amber-950 dark:text-amber-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </span>
                            <span class="size-4 rounded-full border-2 flex items-center justify-center"
                                :class="actionType === 'menu' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                <span class="size-1.5 rounded-full bg-white" x-show="actionType === 'menu'"></span>
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Return to Main Menu</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Returns caller to the main menu.</span>
                    </label>

                    <!-- Goodbye Option -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                        :class="actionType === 'goodbye' ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-950/20 ring-1 ring-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
                        <input type="radio" name="action_type" value="goodbye" x-model="actionType" class="sr-only" />
                        <div class="flex items-center justify-between mb-2">
                            <span class="size-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center dark:bg-gray-800 dark:text-gray-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z" />
                                </svg>
                            </span>
                            <span class="size-4 rounded-full border-2 flex items-center justify-center"
                                :class="actionType === 'goodbye' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'">
                                <span class="size-1.5 rounded-full bg-white" x-show="actionType === 'goodbye'"></span>
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">End Call</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Plays goodbye and disconnects.</span>
                    </label>
                </div>
            </div>

            <!-- Section 3: Audio Playback & Replacement -->
            <div x-show="actionType === 'audio' || actionType === 'sms' || actionType === 'contact_request'" class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">3. Audio Presentation / Confirmation Audio</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage the recording or voice fallback played to confirm the caller's selection.</p>
                </div>

                <!-- Existing Audio Player (if present) -->
                @if ($option->hasAudio() && $option->resolved_audio_url)
                    <div class="p-4 rounded-xl border border-blue-200/80 bg-blue-50/40 dark:border-blue-900/40 dark:bg-blue-950/20">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full bg-blue-500 animate-pulse"></span>
                                <span class="text-xs font-bold text-blue-900 dark:text-blue-300 uppercase tracking-wider">
                                    Current Active Recording
                                </span>
                            </div>
                            <span class="text-xs font-mono font-medium text-blue-700 dark:text-blue-300 truncate max-w-xs">
                                {{ basename($option->audio_path) }}
                            </span>
                        </div>
                        <audio controls class="w-full h-9 rounded-lg" preload="metadata">
                            <source src="{{ $option->resolved_audio_url }}" type="audio/mpeg">
                            Your browser does not support audio playback.
                        </audio>
                        <div class="mt-2.5 pt-2 border-t border-blue-200/60 dark:border-blue-900/40 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 font-medium">
                                <input type="checkbox" name="remove_audio_file" value="1" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span>Delete this recording from server and revert to Text-to-Speech fallback</span>
                            </label>
                        </div>
                    </div>
                @else
                    <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 dark:border-amber-900/40 dark:bg-amber-950/20 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>No audio file uploaded yet. Twilio will read the Text-to-Speech fallback message below.</span>
                    </div>
                @endif

                <!-- Upload New Audio to Replace -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                        {{ $option->hasAudio() ? 'Replace Audio Recording (Upload new MP3 / WAV)' : 'Upload Audio Presentation (MP3 / WAV)' }}
                    </label>
                    <div class="relative flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-2xl transition cursor-pointer"
                        :class="selectedFileName 
                            ? 'border-emerald-500 bg-emerald-50/30 dark:border-emerald-500 dark:bg-emerald-950/20' 
                            : (isDragging ? 'border-brand-500 bg-brand-50/40 dark:border-brand-500 dark:bg-brand-950/30' : 'border-gray-300 bg-gray-50/50 hover:bg-gray-50 hover:border-brand-400 dark:border-gray-700 dark:bg-gray-800/30')"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; if ($event.dataTransfer?.files?.length) { $refs.fileInput.files = $event.dataTransfer.files; handleFileSelect({ target: $refs.fileInput }); }">

                        <template x-if="!selectedFileName">
                            <div class="text-center">
                                <svg class="size-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $option->hasAudio() ? 'Select a replacement audio file' : 'Select an audio file from your computer' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Supported: MP3, WAV, M4A &bull; Max 20MB &bull; Replaces instantly on phone line
                                </p>
                            </div>
                        </template>

                        <template x-if="selectedFileName">
                            <div class="w-full text-center space-y-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100/70 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 text-xs font-semibold">
                                    <svg class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="truncate max-w-[240px]" x-text="selectedFileName"></span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-mono text-[11px]" x-text="'(' + selectedFileSize + ')'"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    New file selected! Save the form below to apply.
                                </p>
                            </div>
                        </template>

                        <input type="file" name="audio_file" x-ref="fileInput" accept=".mp3,.wav,.m4a,audio/*"
                            @change="handleFileSelect($event)"
                            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                    </div>

                    <!-- Instant Audio Preview in Edit View -->
                    <div x-show="selectedFileAudioUrl" class="mt-3 p-3 rounded-xl bg-gray-50 border border-gray-200 dark:bg-gray-800/60 dark:border-gray-700 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Preview Selected File Before Saving:
                            </span>
                            <button type="button" @click.prevent="resetFile()" class="text-rose-600 hover:text-rose-700 text-xs font-medium">
                                Remove / Reset
                            </button>
                        </div>
                        <audio :src="selectedFileAudioUrl" controls class="w-full h-8" preload="auto">
                            Your browser does not support audio preview.
                        </audio>
                    </div>
                </div>

                <!-- Fallback Text -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Text-to-Speech Fallback (Amazon Polly Joanna)
                        </label>
                        <span class="text-[11px] text-gray-400">Used if audio file cannot be loaded</span>
                    </div>
                    <textarea name="fallback_text" rows="3" placeholder="Enter message to be spoken clearly over the phone if no recording is present..."
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/30 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition">{{ old('fallback_text', $option->fallback_text) }}</textarea>
                </div>
            </div>

            <!-- Section 4: SMS Message & Destination Link -->
            <div x-show="actionType === 'sms' || actionType === 'contact_request'" class="rounded-2xl border border-purple-200 bg-purple-50/20 p-6 dark:border-purple-900/40 dark:bg-purple-950/10 shadow-xs space-y-4">
                <div class="border-b border-purple-100 dark:border-purple-900/30 pb-4">
                    <h2 class="text-base font-bold text-purple-900 dark:text-purple-300">4. SMS Text Message & Destination Link</h2>
                    <p class="text-xs text-purple-700/70 dark:text-purple-300/60 mt-0.5">The exact text message and destination link (e.g. DiscoverFlowCore.com/learn) sent to the caller.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                        Text Message Content & Destination URL
                    </label>
                    <textarea name="sms_message" rows="3" placeholder="Thank you for your interest in FlowCore! Learn more at: https://DiscoverFlowCore.com/learn"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">{{ old('sms_message', $option->sms_message) }}</textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Include full URLs (e.g. <span class="font-mono text-purple-600 dark:text-purple-400">https://DiscoverFlowCore.com/learn</span>). The SMS message and destination links remain editable at any time without rebuilding the IVR.</p>
                </div>
            </div>

            <!-- Active Toggle & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $option->is_active) ? 'checked' : '' }} class="sr-only peer" />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    <span class="ms-3 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Option is active & accepts caller keypad input
                    </span>
                </label>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ivr.options.index') }}"
                        class="px-5 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-semibold text-white bg-brand-500 rounded-xl hover:bg-brand-600 shadow-sm transition-all">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
