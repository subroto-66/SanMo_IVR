@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        uploadModalOpen: false,
        modalTitle: '',
        modalTargetType: '',
        modalOptionId: null,
        selectedFileName: '',
        selectedFileSize: '',
        selectedFileAudioUrl: null,
        isDragging: false,
        isUploading: false,
        openUploadModal(title, targetType, optionId = null) {
            this.modalTitle = title;
            this.modalTargetType = targetType;
            this.modalOptionId = optionId;
            this.resetFile();
            this.isUploading = false;
            this.uploadModalOpen = true;
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
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                    IVR Audio Management
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Listen, upload, and instantly hot-swap prerecorded audio presentations without code redeployments.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200/60 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Audio Swapping Active
                </span>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 1. System Greeting & Call Flow Audio -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="mb-5 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-brand-500"></span>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Core Call Flow Audio</h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Welcome greetings, primary informational pitch, invalid keypad triggers, and disconnection audio.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($systemAudioSlots as $slot)
                    <div class="flex flex-col justify-between p-5 rounded-2xl border border-gray-200/80 bg-gray-50/40 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-800/30 dark:hover:border-gray-700 transition">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $slot['title'] }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $slot['description'] }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $slot['path'] && $slot['file_exists'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60' : ($slot['path'] ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60') }}">
                                    @if ($slot['path'] && $slot['file_exists'])
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        MP3 Active
                                    @elseif ($slot['path'])
                                        <span class="size-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        File Missing (TTS Active)
                                    @else
                                        <span class="size-1.5 rounded-full bg-amber-500"></span>
                                        TTS Active
                                    @endif
                                </span>
                            </div>

                            <!-- Audio Details & Player -->
                            <div class="mt-4">
                                @if ($slot['path'] && $slot['file_exists'] && $slot['url'])
                                    <div class="space-y-2 p-3 rounded-xl bg-white border border-gray-200/70 dark:bg-gray-900 dark:border-gray-800 shadow-2xs">
                                        <audio controls class="w-full h-8" preload="metadata">
                                            <source src="{{ $slot['url'] }}" type="{{ $slot['meta']['mime'] ?? 'audio/mpeg' }}">
                                            Your browser does not support audio playback.
                                        </audio>
                                        <div class="flex items-center justify-between text-[11px] text-gray-500 font-mono">
                                            <span class="truncate max-w-[180px]" title="{{ basename($slot['path']) }}">
                                                {{ basename($slot['path']) }}
                                            </span>
                                            <div class="flex items-center gap-2">
                                                @if ($slot['meta'])
                                                    <span>{{ number_format($slot['meta']['size'] / 1024, 1) }} KB</span>
                                                @endif
                                                <a href="{{ $slot['url'] }}" target="_blank" download title="Stream or download audio directly" class="text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($slot['path'])
                                    <div class="p-3 rounded-xl bg-rose-50/50 border border-dashed border-rose-200 text-xs text-rose-800 dark:bg-rose-950/20 dark:border-rose-900/40 dark:text-rose-300">
                                        <div class="flex items-center gap-1.5 font-semibold mb-1 text-rose-700 dark:text-rose-400">
                                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Recording Missing From Server (Protected by TTS)
                                        </div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                                            The referenced file <code class="font-mono text-gray-700 dark:text-gray-300">{{ basename($slot['path']) }}</code> was not found in storage. Callers are safely hearing Joanna TTS fallback:
                                        </p>
                                        <span class="italic text-gray-600 dark:text-gray-300">"{{ Str::limit($slot['fallback'] ?: 'Default telephony voice prompt', 90) }}"</span>
                                    </div>
                                @else
                                    <div class="p-3 rounded-xl bg-white border border-dashed border-gray-300 text-xs text-gray-600 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-400">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-0.5">Amazon Polly Joanna Fallback:</span>
                                        <span class="italic text-gray-500 dark:text-gray-400">"{{ Str::limit($slot['fallback'] ?: 'Default telephony voice prompt', 90) }}"</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 pt-3.5 border-t border-gray-200/60 dark:border-gray-800 flex items-center justify-between">
                            <button type="button"
                                @click="openUploadModal('{{ $slot['title'] }}', '{{ $slot['target_type'] }}')"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-800 border border-gray-300 shadow-2xs hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                {{ $slot['path'] ? 'Replace Recording' : 'Upload MP3/WAV' }}
                            </button>

                            @if ($slot['path'])
                                <form method="POST" action="{{ route('admin.ivr.audio.delete') }}" onsubmit="return confirm('Remove this audio recording and revert to text-to-speech fallback?');">
                                    @csrf
                                    <input type="hidden" name="target_type" value="{{ $slot['target_type'] }}">
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400">
                                        Revert to TTS
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Keypad Options Audio (1 through N) -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="mb-5 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-brand-500"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Keypad Options Audio</h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Prerecorded presentations triggered when callers press digits on their telephone keypad.
                    </p>
                </div>
                <a href="{{ route('admin.ivr.options.create') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Keypad Option
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($optionAudioSlots as $slot)
                    @php
                        $optDigit = $slot['option']->digit;
                        $dialSubtext = match($optDigit) {
                            '1' => 'INFO',
                            '2' => 'ABC',
                            '3' => 'DEF',
                            '4' => 'GHI',
                            '5' => 'JKL',
                            '6' => 'MNO',
                            '7' => 'PQRS',
                            '8' => 'TUV',
                            '9' => 'WXYZ',
                            '0' => 'OPER',
                            '*' => 'STAR',
                            '#' => 'HASH',
                            default => 'KEY',
                        };
                    @endphp
                    <div class="flex flex-col justify-between p-5 rounded-2xl border border-gray-200/80 bg-gray-50/40 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-800/30 dark:hover:border-gray-700 transition">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <!-- Tactile Keypad Key Badge -->
                                    <div class="relative flex flex-col items-center justify-center size-11 rounded-xl bg-gradient-to-b from-gray-50 to-gray-200 border-b-2 border-gray-300 shadow-2xs dark:from-gray-800 dark:to-gray-900 dark:border-gray-950 ring-1 ring-gray-900/5 dark:ring-white/10 shrink-0 select-none">
                                        <span class="font-mono text-base font-black leading-none text-gray-900 dark:text-white">
                                            {{ $optDigit }}
                                        </span>
                                        <span class="text-[8px] font-extrabold tracking-widest text-gray-500 dark:text-gray-400 uppercase leading-none mt-0.5">
                                            {{ $dialSubtext }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $slot['option']->title }}</h3>
                                        <span class="text-[11px] font-medium text-gray-400">{{ ucfirst($slot['option']->action_type) }} Action</span>
                                    </div>
                                </div>

                                <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $slot['path'] && $slot['file_exists'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60' : ($slot['path'] ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60') }}">
                                    @if ($slot['path'] && $slot['file_exists'])
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        MP3 Audio
                                    @elseif ($slot['path'])
                                        <span class="size-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        File Missing (TTS Active)
                                    @else
                                        <span class="size-1.5 rounded-full bg-amber-500"></span>
                                        Polly TTS
                                    @endif
                                </span>
                            </div>

                            <!-- Audio Details & Player -->
                            <div class="mt-4">
                                @if ($slot['path'] && $slot['file_exists'] && $slot['url'])
                                    <div class="space-y-2 p-3 rounded-xl bg-white border border-gray-200/70 dark:bg-gray-900 dark:border-gray-800 shadow-2xs">
                                        <audio controls class="w-full h-8" preload="metadata">
                                            <source src="{{ $slot['url'] }}" type="{{ $slot['meta']['mime'] ?? 'audio/mpeg' }}">
                                            Your browser does not support audio playback.
                                        </audio>
                                        <div class="flex items-center justify-between text-[11px] text-gray-500 font-mono">
                                            <span class="truncate max-w-[180px]" title="{{ basename($slot['path']) }}">
                                                {{ basename($slot['path']) }}
                                            </span>
                                            <div class="flex items-center gap-2">
                                                @if ($slot['meta'])
                                                    <span>{{ number_format($slot['meta']['size'] / 1024, 1) }} KB</span>
                                                @endif
                                                <a href="{{ $slot['url'] }}" target="_blank" download title="Stream or download audio directly" class="text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($slot['path'])
                                    <div class="p-3 rounded-xl bg-rose-50/50 border border-dashed border-rose-200 text-xs text-rose-800 dark:bg-rose-950/20 dark:border-rose-900/40 dark:text-rose-300">
                                        <div class="flex items-center gap-1.5 font-semibold mb-1 text-rose-700 dark:text-rose-400">
                                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Recording Missing From Server (Protected by TTS)
                                        </div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                                            The referenced file <code class="font-mono text-gray-700 dark:text-gray-300">{{ basename($slot['path']) }}</code> was not found in storage. Callers safely hear Joanna TTS:
                                        </p>
                                        <span class="italic text-gray-600 dark:text-gray-300">"{{ Str::limit($slot['fallback'] ?: 'You selected ' . $slot['option']->title, 90) }}"</span>
                                    </div>
                                @else
                                    <div class="p-3 rounded-xl bg-white border border-dashed border-gray-300 text-xs text-gray-600 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-400">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-0.5">TTS Fallback:</span>
                                        <span class="italic text-gray-500 dark:text-gray-400">"{{ Str::limit($slot['fallback'] ?: 'You selected ' . $slot['option']->title, 90) }}"</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 pt-3.5 border-t border-gray-200/60 dark:border-gray-800 flex items-center justify-between">
                            <button type="button"
                                @click="openUploadModal('Option {{ $slot['option']->digit }}: {{ $slot['option']->title }}', 'option', {{ $slot['option']->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-800 border border-gray-300 shadow-2xs hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                {{ $slot['path'] ? 'Replace Recording' : 'Upload MP3/WAV' }}
                            </button>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.ivr.options.edit', $slot['option']) }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                    Edit Settings
                                </a>
                                @if ($slot['path'])
                                    <form method="POST" action="{{ route('admin.ivr.audio.delete') }}" onsubmit="return confirm('Remove audio recording for this option?');">
                                        @csrf
                                        <input type="hidden" name="target_type" value="option">
                                        <input type="hidden" name="option_id" value="{{ $slot['option']->id }}">
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400">
                                            Revert
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Audio Upload / Replace Modal -->
        <div x-show="uploadModalOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-xs"
            @keydown.escape.window="if (!isUploading) { uploadModalOpen = false; resetFile(); }">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 dark:border dark:border-gray-800"
                @click.away="if (!isUploading) { uploadModalOpen = false; resetFile(); }">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="'Upload Audio: ' + modalTitle"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select and preview audio before saving.</p>
                    </div>
                    <button type="button" @click="uploadModalOpen = false; resetFile();" :disabled="isUploading" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.ivr.audio.upload') }}" enctype="multipart/form-data" @submit="isUploading = true" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="target_type" :value="modalTargetType">
                    <input type="hidden" name="option_id" :value="modalOptionId">

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Audio File (MP3, WAV, M4A &bull; Max 20MB)
                        </label>
                        
                        <!-- File Upload Dropzone -->
                        <div class="relative flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-2xl transition cursor-pointer"
                            :class="selectedFileName 
                                ? 'border-emerald-500 bg-emerald-50/30 dark:border-emerald-500 dark:bg-emerald-950/20' 
                                : (isDragging ? 'border-brand-500 bg-brand-50/40 dark:border-brand-500 dark:bg-brand-950/30' : 'border-gray-300 bg-gray-50/50 hover:bg-gray-50 hover:border-brand-400 dark:border-gray-700 dark:bg-gray-800/30')"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; if ($event.dataTransfer?.files?.length) { $refs.fileInput.files = $event.dataTransfer.files; handleFileSelect({ target: $refs.fileInput }); }">

                            <!-- When no file is selected yet -->
                            <template x-if="!selectedFileName">
                                <div class="text-center">
                                    <svg class="size-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Click to select or drag & drop an audio file
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        MP3, WAV, M4A up to 20MB
                                    </p>
                                </div>
                            </template>

                            <!-- When file has been selected -->
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
                                        File selected! Ready to upload.
                                    </p>
                                </div>
                            </template>

                            <input type="file" name="audio_file" x-ref="fileInput" required accept=".mp3,.wav,.m4a,audio/*"
                                @change="handleFileSelect($event)"
                                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                        </div>

                        <!-- Instant Audio Preview in Modal -->
                        <div x-show="selectedFileAudioUrl" class="mt-3 p-3 rounded-xl bg-gray-50 border border-gray-200 dark:bg-gray-800/60 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Preview Audio Before Upload:
                                </span>
                                <button type="button" @click.prevent="resetFile()" class="text-rose-600 hover:text-rose-700 text-xs font-medium">
                                    Remove / Change
                                </button>
                            </div>
                            <audio :src="selectedFileAudioUrl" controls class="w-full h-8" preload="auto">
                                Your browser does not support audio preview.
                            </audio>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="uploadModalOpen = false; resetFile();" :disabled="isUploading"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isUploading || !selectedFileName"
                            class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-brand-500 rounded-xl hover:bg-brand-600 transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="isUploading" class="size-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isUploading ? 'Uploading Recording...' : 'Upload Recording'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
