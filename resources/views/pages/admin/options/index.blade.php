@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex size-2 rounded-full bg-brand-500 animate-pulse"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Telephony Routing</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl mt-0.5">
                    IVR Keypad Menu Options
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage telephone keypad DTMF responses, prerecorded audio presentations, and SMS dispatches.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.ivr.options.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-600 hover:shadow-md focus:ring-2 focus:ring-brand-500/20">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Keypad Option
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300 shadow-xs">
                <svg class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-300 shadow-xs">
                <svg class="size-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Options Table Card -->
        <div class="rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-semibold text-gray-500 dark:bg-gray-800/40 dark:border-gray-800 uppercase tracking-wider">
                        <tr>
                            <th class="py-4 px-5 text-start">Keypad Digit</th>
                            <th class="py-4 px-5 text-start">Option Title & Description</th>
                            <th class="py-4 px-5 text-start">Action Type</th>
                            <th class="py-4 px-5 text-start">Audio Status</th>
                            <th class="py-4 px-5 text-start">Status</th>
                            <th class="py-4 px-5 text-end">Manage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @php
                            $subTextMap = [
                                '1' => 'INFO',
                                '2' => 'ABC',
                                '3' => 'DEF',
                                '4' => 'GHI',
                                '5' => 'JKL',
                                '6' => 'MNO',
                                '7' => 'PQRS',
                                '8' => 'TUV',
                                '9' => 'WXYZ',
                                '0' => '+',
                                '*' => 'STAR',
                                '#' => 'POUND',
                            ];
                        @endphp

                        @forelse ($options as $opt)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <!-- Professional Keypad Digit Badge -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-11 flex-col items-center justify-center rounded-xl border border-gray-200 bg-gradient-to-b from-white to-gray-100 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-850 ring-1 ring-black/5 dark:ring-white/5 transition group-hover:border-brand-300">
                                            <span class="font-mono text-base font-extrabold text-gray-900 dark:text-white leading-none">
                                                {{ $opt->digit }}
                                            </span>
                                            <span class="text-[8px] font-bold tracking-wider text-gray-400 dark:text-gray-500 mt-0.5">
                                                {{ $subTextMap[$opt->digit] ?? 'KEY' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Title / Description -->
                                <td class="py-4 px-5 max-w-xs sm:max-w-sm">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $opt->title }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 font-mono text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200/60 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800/60 rounded px-1.5 py-0.5">
                                            {{ $opt->activity_label }}
                                        </span>
                                    </div>
                                    @if ($opt->destination_url)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-mono text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 px-2 py-0.5 rounded border border-purple-200/60 dark:border-purple-800/60">
                                                <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                {{ $opt->destination_url }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($opt->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                            {{ $opt->description }}
                                        </p>
                                    @endif
                                </td>

                                <!-- Action Type Badge -->
                                <td class="py-4 px-5">
                                    @if ($opt->action_type === 'audio')
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-900/40">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                            </svg>
                                            Audio Playback
                                        </span>
                                    @elseif ($opt->action_type === 'sms')
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-900/40">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                            </svg>
                                            SMS Link & Audio
                                        </span>
                                    @elseif ($opt->action_type === 'contact_request')
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/40">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            Contact Request
                                        </span>
                                    @elseif ($opt->action_type === 'menu')
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/40">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Menu Navigation
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                            Goodbye / End
                                        </span>
                                    @endif
                                </td>

                                <!-- Audio Status & Inline Preview -->
                                <td class="py-4 px-5">
                                    @if ($opt->hasAudio())
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/40">
                                                <svg class="size-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                                </svg>
                                                Prerecorded Audio
                                            </span>
                                            @if ($opt->resolved_audio_url)
                                                <audio controls class="h-7 w-28 scale-90" preload="metadata">
                                                    <source src="{{ $opt->resolved_audio_url }}" type="audio/mpeg">
                                                </audio>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            <span class="size-1.5 rounded-full bg-amber-400"></span>
                                            Text-to-Speech
                                        </span>
                                    @endif
                                </td>

                                <!-- Active Status Toggle -->
                                <td class="py-4 px-5">
                                    <form method="POST" action="{{ route('admin.ivr.options.toggle', $opt) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold cursor-pointer transition-all shadow-2xs
                                                {{ $opt->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                                            <span class="size-1.5 rounded-full {{ $opt->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                            {{ $opt->is_active ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-5 text-end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.ivr.options.edit', $opt) }}"
                                            class="p-2 rounded-lg text-gray-600 hover:text-brand-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-brand-400 dark:hover:bg-gray-800 transition"
                                            title="Edit Option & Audio">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.ivr.options.destroy', $opt) }}"
                                            onsubmit="return confirm('Permanently delete Option {{ $opt->digit }} ({{ $opt->title }})?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 rounded-lg text-gray-600 hover:text-rose-600 hover:bg-rose-50 dark:text-gray-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/40 transition"
                                                title="Delete Option">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-500">
                                    <div class="mx-auto size-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mb-3 dark:bg-gray-800">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300">No Keypad Options Configured</p>
                                    <p class="text-xs text-gray-400 mt-1">Click "New Keypad Option" to assign a phone digit response.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
