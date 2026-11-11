@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                    Inbound Call History & Logs
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Real-time audit log of callers, dialed digits, interactive sessions, and telephony events.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live IVR Receiver Ready
                </span>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <form method="GET" action="{{ route('admin.ivr.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Search -->
                <div class="sm:col-span-5 relative">
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search phone number, Call SID, or menu option..."
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/50 ps-9 pe-4 py-2.5 text-xs text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-4">
                    <select name="status"
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-3.5 py-2.5 text-xs font-medium text-gray-800 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <option value="">All Call Statuses</option>
                        <option value="contact" {{ $status === 'contact' ? 'selected' : '' }}>📞 Contact Requested Only</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed (Success)</option>
                        <option value="in-progress" {{ $status === 'in-progress' ? 'selected' : '' }}>In Progress (Active)</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed / Error</option>
                        <option value="busy" {{ $status === 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="no-answer" {{ $status === 'no-answer' ? 'selected' : '' }}>No Answer</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="sm:col-span-3 flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white hover:bg-brand-600 transition shadow-xs">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter Logs
                    </button>
                    @if ($search || $status || $date)
                        <a href="{{ route('admin.ivr.logs.index') }}"
                            class="rounded-xl border border-gray-200 bg-gray-100 px-3 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Call Logs Table -->
        <div class="rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-100 text-[11px] text-gray-500 dark:bg-gray-800/60 dark:border-gray-800 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 text-start">Caller Phone</th>
                            <th class="py-3.5 px-4 text-start">Dialed Key & Selection</th>
                            <th class="py-3.5 px-4 text-start">Duration</th>
                            <th class="py-3.5 px-4 text-start">Status</th>
                            <th class="py-3.5 px-4 text-start">Call SID</th>
                            <th class="py-3.5 px-4 text-start">Time</th>
                            <th class="py-3.5 px-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($callLogs as $log)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <!-- Caller Phone -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="size-7 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center shrink-0 dark:bg-gray-800 dark:text-gray-300">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <span class="font-mono font-semibold text-xs text-gray-900 dark:text-white block">
                                                {{ $log->from_number ?: 'Anonymous / Restricted' }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 block">
                                                To: {{ $log->to_number ?: 'System Line' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Selected Option / Caller Activity Journey -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $steps = $log->selection_steps;
                                    @endphp

                                    @if (!empty($steps))
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
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
                                                        <svg class="size-3 text-gray-400 shrink-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    @endif

                                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 text-xs shadow-2xs">
                                                        @if ($digit)
                                                            <span class="size-4 rounded bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-mono text-[10px] font-black inline-flex items-center justify-center shrink-0">
                                                                {{ $digit }}
                                                            </span>
                                                        @endif
                                                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[140px]" title="{{ $label }}">
                                                            {{ $label }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if ($log->hasContactRequest())
                                                <div class="flex items-center gap-1 mt-0.5">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800">
                                                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        📞 Contact Requested
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 italic">
                                            <span class="size-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                            No Key Dialed
                                        </span>
                                    @endif
                                </td>

                                <!-- Duration -->
                                <td class="py-3.5 px-4 text-xs font-mono font-medium text-gray-600 dark:text-gray-300">
                                    {{ $log->formatted_duration }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    @if ($log->status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/60">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Completed
                                        </span>
                                    @elseif ($log->status === 'in-progress')
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800/60">
                                            <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800/60">
                                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Call SID -->
                                <td class="py-3.5 px-4 font-mono text-[11px] text-gray-400 truncate max-w-[130px]" title="{{ $log->call_sid }}">
                                    {{ $log->call_sid }}
                                </td>

                                <!-- Date & Time -->
                                <td class="py-3.5 px-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="block text-gray-900 dark:text-white font-medium">{{ $log->created_at->format('M d, Y') }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $log->created_at->format('h:i A') }}</span>
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 text-end">
                                    <a href="{{ route('admin.ivr.logs.show', $log) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-brand-600 hover:text-brand-700 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950/50 transition">
                                        Audit Detail
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="size-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">No call logs found</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Calls received by your Twilio phone number will appear here automatically in real time.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($callLogs->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $callLogs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
