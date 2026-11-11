@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                    Twilio IVR System Overview
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    24/7 Automated Inbound Telephony, Prerecorded Information & Recruitment Line
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ivr.audio.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    Manage Audio Recordings
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <!-- Active Options -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Keypad Options</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        {{ $activeOptions }} Active
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $totalOptions }}</span>
                    <span class="text-xs text-gray-400">digits mapped</span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('admin.ivr.options.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1">
                        Configure dialpad &rarr;
                    </a>
                </div>
            </div>

            <!-- Audio Presentations -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Audio Library</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60">
                        Zero-AWS
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $totalAudioFiles }}</span>
                    <span class="text-xs text-gray-400">recordings online</span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('admin.ivr.audio.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1">
                        Swap audio files &rarr;
                    </a>
                </div>
            </div>

            <!-- Inbound Calls Today -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Calls Today</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        24/7 Live
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $todayCalls }}</span>
                    <span class="text-xs text-gray-400">({{ $totalCalls }} all-time)</span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('admin.ivr.logs.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1">
                        View call logs &rarr;
                    </a>
                </div>
            </div>

            <!-- SMS Sent -->
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SMS Dispatched</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2.5 py-0.5 text-[11px] font-bold text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/60">
                        Messaging API
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $smsSentCount }}</span>
                    <span class="text-xs text-gray-400">links sent</span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('admin.ivr.options.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 inline-flex items-center gap-1">
                        View SMS options &rarr;
                    </a>
                </div>
            </div>

            <!-- FlowCore Contact Requests -->
            <div class="rounded-2xl border {{ $contactRequestsCount > 0 ? 'border-amber-300 dark:border-amber-700/60 bg-amber-50/30 dark:bg-amber-950/20' : 'border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Requests</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-300/80 dark:border-amber-700">
                        Option 5
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black tracking-tight {{ $contactRequestsCount > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">{{ $contactRequestsCount }}</span>
                    <span class="text-xs text-gray-400">follow-ups</span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('admin.ivr.logs.index', ['status' => 'contact']) }}" class="font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 inline-flex items-center gap-1">
                        Review leads &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Telephony Status & Webhook Summary -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        Twilio Telephony & Webhook Endpoints
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Copy these exact webhook URLs into your Twilio Console phone number configuration.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ config('twilio.account_sid') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800' }}">
                        <span class="size-2 rounded-full {{ config('twilio.account_sid') ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        {{ config('twilio.account_sid') ? 'Twilio SID Configured' : 'Twilio SID Pending (.env)' }}
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Voice Webhook -->
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/70 dark:bg-gray-800/40 dark:border-gray-800"
                    x-data="{ copied: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">Voice Inbound Webhook</span>
                        <button type="button" @click="navigator.clipboard.writeText('{{ route('twilio.voice') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer"
                            x-text="copied ? 'Copied!' : 'Copy'"></button>
                    </div>
                    <div class="font-mono text-xs font-semibold text-gray-900 dark:text-gray-100 break-all select-all">
                        {{ route('twilio.voice') }}
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-400">HTTP POST &bull; Twilio Voice "A Call Comes In"</span>
                </div>

                <!-- DTMF Menu Callback -->
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/70 dark:bg-gray-800/40 dark:border-gray-800"
                    x-data="{ copied: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">DTMF Menu Callback</span>
                        <button type="button" @click="navigator.clipboard.writeText('{{ route('twilio.menu') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer"
                            x-text="copied ? 'Copied!' : 'Copy'"></button>
                    </div>
                    <div class="font-mono text-xs font-semibold text-gray-900 dark:text-gray-100 break-all select-all">
                        {{ route('twilio.menu') }}
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-400">HTTP POST &bull; Twilio &lt;Gather&gt; action endpoint</span>
                </div>

                <!-- Call Status Callback -->
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/70 dark:bg-gray-800/40 dark:border-gray-800"
                    x-data="{ copied: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">Call Status Callback</span>
                        <button type="button" @click="navigator.clipboard.writeText('{{ route('twilio.status') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer"
                            x-text="copied ? 'Copied!' : 'Copy'"></button>
                    </div>
                    <div class="font-mono text-xs font-semibold text-gray-900 dark:text-gray-100 break-all select-all">
                        {{ route('twilio.status') }}
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-400">HTTP POST &bull; Duration & status tracking</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Active IVR Keypad Options -->
            <div class="lg:col-span-1 rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Active Keypad Flow</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Caller dialpad options</p>
                    </div>
                    <a href="{{ route('admin.ivr.options.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Manage &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($activeOptionsList as $opt)
                        @php
                            $dialSubtext = match($opt->digit) {
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
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                            <div class="flex items-center gap-3">
                                <!-- Tactile Keypad Key Badge -->
                                <div class="relative flex flex-col items-center justify-center size-10 rounded-xl bg-gradient-to-b from-gray-50 to-gray-200 border-b-2 border-gray-300 shadow-2xs dark:from-gray-800 dark:to-gray-900 dark:border-gray-950 ring-1 ring-gray-900/5 dark:ring-white/10 shrink-0 select-none">
                                    <span class="font-mono text-sm font-black leading-none text-gray-900 dark:text-white">
                                        {{ $opt->digit }}
                                    </span>
                                    <span class="text-[8px] font-extrabold tracking-widest text-gray-500 dark:text-gray-400 uppercase leading-none mt-0.5">
                                        {{ $dialSubtext }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-gray-900 dark:text-white">{{ $opt->title }}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            {{ ucfirst($opt->action_type) }}
                                        </span>
                                        @if ($opt->sms_enabled)
                                            <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">&bull; SMS</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                @if ($opt->hasAudio())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                        </svg>
                                        Audio
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                                        TTS
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 py-6 text-center">No active options configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Inbound Call Activity -->
            <div class="lg:col-span-2 rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Recent Call Activity</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Live caller interactions</p>
                    </div>
                    <a href="{{ route('admin.ivr.logs.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        View all logs &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead class="border-b border-gray-100 text-[11px] text-gray-400 dark:border-gray-800 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="pb-3 text-start">Caller Phone</th>
                                <th class="pb-3 text-start">Dialed Key</th>
                                <th class="pb-3 text-start">Duration</th>
                                <th class="pb-3 text-start">Status</th>
                                <th class="pb-3 text-end">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($recentCalls as $call)
                                @php
                                    $steps = $call->selection_steps;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                    <td class="py-3 font-mono font-semibold text-xs text-gray-900 dark:text-white">
                                        <a href="{{ route('admin.ivr.logs.show', $call->id) }}" class="hover:text-brand-600 dark:hover:text-brand-400">
                                            {{ $call->from_number ?: 'Anonymous' }}
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex flex-col gap-1">
                                            @if ($call->hasContactRequest())
                                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 dark:bg-amber-950/60 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:text-amber-300 w-fit border border-amber-300 dark:border-amber-700">
                                                    📞 Contact Requested
                                                </span>
                                            @endif

                                            @if (count($steps) > 0)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @foreach ($steps as $idx => $step)
                                                        @if ($idx > 0)
                                                            <span class="text-gray-300 dark:text-gray-600 text-[10px] font-bold">&rarr;</span>
                                                        @endif
                                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                            @if (!empty($step['digit']))
                                                                <span class="font-mono font-black text-gray-900 dark:text-white">{{ $step['digit'] }}</span>
                                                            @endif
                                                            <span>{{ $step['label'] }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif ($call->selected_option)
                                                <span class="text-xs text-gray-600 dark:text-gray-300">{{ $call->selected_option }}</span>
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">No Key Dialed</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $call->formatted_duration }}
                                    </td>
                                    <td class="py-3">
                                        @if ($call->status === 'completed')
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                <span class="size-1 rounded-full bg-emerald-500"></span>
                                                Completed
                                            </span>
                                        @elseif ($call->status === 'in-progress')
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400">
                                                <span class="size-1 rounded-full bg-blue-500 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">
                                                {{ ucfirst($call->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-end text-xs text-gray-400">
                                        {{ $call->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-gray-400">
                                        No inbound calls recorded yet. Phone dials will appear here in real-time.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
