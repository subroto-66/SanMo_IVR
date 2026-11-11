@extends('layouts.app')

@section('content')
    <div class="max-w-4xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.ivr.logs.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Call Logs
                </a>
                <div class="flex items-center gap-3 mt-1.5">
                    <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                        Call Audit Session
                    </h1>
                    <span class="font-mono text-xs px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $callLog->call_sid }}
                    </span>
                </div>
            </div>
            <div>
                @if ($callLog->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        Completed
                    </span>
                @elseif ($callLog->status === 'in-progress')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800">
                        <span class="size-2 rounded-full bg-blue-500 animate-pulse"></span>
                        In Progress
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        {{ ucfirst($callLog->status) }}
                    </span>
                @endif
            </div>
        </div>

        @if ($callLog->hasContactRequest())
            <!-- Contact Request Banner -->
            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-800/80 dark:bg-amber-950/40 shadow-xs flex items-start gap-4">
                <div class="size-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a11.042 11.042 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                            FlowCore Personal Contact Follow-up Requested
                        </h3>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-300">
                            Action Required
                        </span>
                    </div>
                    <p class="text-xs text-amber-800 dark:text-amber-300/80 mt-1">
                        The caller pressed <span class="font-bold">Option 5 (Contact Request)</span> requesting a personal follow-up from a FlowCore representative.
                    </p>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <a href="tel:{{ $callLog->from_number }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 text-white font-semibold hover:bg-amber-700 shadow-xs transition">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a11.042 11.042 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Call Back {{ $callLog->from_number }}
                        </a>
                        <span class="font-mono text-amber-700 dark:text-amber-400 text-xs">
                            Logged: {{ $callLog->created_at->format('M d, Y h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Telephony Summary Card -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Telephony Session Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-1">Caller (From)</span>
                    <span class="font-mono font-bold text-sm text-gray-900 dark:text-white block">{{ $callLog->from_number ?: 'Anonymous' }}</span>
                </div>
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-1">Dialed Line (To)</span>
                    <span class="font-mono font-bold text-sm text-gray-900 dark:text-white block">{{ $callLog->to_number ?: 'N/A' }}</span>
                </div>
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-1">Call Duration</span>
                    <span class="font-bold text-sm text-gray-900 dark:text-white block">{{ $callLog->formatted_duration }}</span>
                </div>
                <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-1">Keypad Activity Summary</span>
                    <span class="font-bold text-sm text-brand-600 dark:text-brand-400 block truncate" title="{{ $callLog->selected_option }}">
                        {{ $callLog->selected_option ?: 'None / Dropped' }}
                    </span>
                </div>
            </div>

            <!-- Caller Journey Sequence Breakdown -->
            @php
                $steps = $callLog->selection_steps;
            @endphp
            @if (!empty($steps))
                <div class="mt-5 p-4 rounded-xl bg-gray-50/60 border border-gray-200/80 dark:bg-gray-800/30 dark:border-gray-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 block mb-2.5">
                        Caller Activity Path:
                    </span>
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach ($steps as $idx => $step)
                            @php
                                $digit = is_array($step) ? ($step['digit'] ?? null) : null;
                                $label = is_array($step) ? ($step['label'] ?? '') : $step;
                                if (!is_array($step) && preg_match('/^(\S+)\s*[—\-]\s*(.+)$/u', $step, $m)) {
                                    $digit = $m[1];
                                    $label = $m[2];
                                }
                            @endphp

                            @if ($idx > 0)
                                <svg class="size-4 text-gray-400 shrink-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            @endif

                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xs">
                                @if ($digit)
                                    <span class="size-5 rounded-md bg-brand-500 text-white font-mono text-xs font-black inline-flex items-center justify-center shrink-0">
                                        {{ $digit }}
                                    </span>
                                @endif
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="p-3.5 rounded-xl bg-gray-50/40 border border-gray-100 dark:bg-gray-800/30 dark:border-gray-800 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Session Connected</span>
                    <span class="font-mono text-gray-800 dark:text-gray-200">{{ $callLog->started_at ? $callLog->started_at->format('Y-m-d H:i:s T') : 'N/A' }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50/40 border border-gray-100 dark:bg-gray-800/30 dark:border-gray-800 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Session Terminated</span>
                    <span class="font-mono text-gray-800 dark:text-gray-200">{{ $callLog->ended_at ? $callLog->ended_at->format('Y-m-d H:i:s T') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Event Timeline Audit Trail -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-1">Telephony Event Audit Trail</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Chronological sequence of telephony webhook transitions and caller keypad interactions.
            </p>

            @php
                $events = $callLog->metadata['events'] ?? [];
            @endphp

            @if (!empty($events))
                <div class="relative border-s-2 border-gray-200 dark:border-gray-700 ms-3 space-y-6">
                    @foreach ($events as $idx => $event)
                        <div class="ms-6 relative">
                            <span class="absolute -start-[31px] top-0 flex size-6 items-center justify-center rounded-full bg-brand-500 text-white ring-4 ring-white dark:ring-gray-900 text-xs font-bold">
                                {{ $idx + 1 }}
                            </span>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ ucwords(str_replace('_', ' ', $event['event'] ?? 'Event')) }}
                                </h3>
                                <span class="text-[11px] font-mono text-gray-400">
                                    {{ isset($event['time']) ? \Carbon\Carbon::parse($event['time'])->format('H:i:s') : '' }}
                                </span>
                            </div>
                            @if (!empty($event['data']))
                                <div class="mt-2.5 p-3 rounded-xl bg-gray-50 border border-gray-200/60 font-mono text-xs text-gray-700 dark:bg-gray-800/60 dark:border-gray-800 dark:text-gray-300 overflow-x-auto">
                                    <pre class="whitespace-pre-wrap text-[11px] leading-relaxed">{{ json_encode($event['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-xs text-gray-400">
                    No granular events recorded for this session.
                </div>
            @endif
        </div>
    </div>
@endsection
