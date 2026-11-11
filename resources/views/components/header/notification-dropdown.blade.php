{{-- Dynamic Real-Time IVR Notification Dropdown --}}
@php
    use App\Models\IvrCallLog;
    use App\Models\IvrSetting;

    $notifyInAppEnabled = filter_var(IvrSetting::get('notify_in_app_enabled', true), FILTER_VALIDATE_BOOLEAN);
    $notifyContact = filter_var(IvrSetting::get('notify_in_app_contact_requests', true), FILTER_VALIDATE_BOOLEAN);
    $notifyInbound = filter_var(IvrSetting::get('notify_in_app_inbound_calls', true), FILTER_VALIDATE_BOOLEAN);
    $notifySms = filter_var(IvrSetting::get('notify_in_app_sms_activity', true), FILTER_VALIDATE_BOOLEAN);
    $notifyMissed = filter_var(IvrSetting::get('notify_in_app_missed_calls', true), FILTER_VALIDATE_BOOLEAN);

    $notifications = collect();
    $unreadCount = 0;

    if ($notifyInAppEnabled) {
        $recentLogs = IvrCallLog::orderBy('created_at', 'desc')->take(12)->get();

        foreach ($recentLogs as $log) {
            $isContactRequest = !empty($log->metadata['has_contact_request']) || $log->selected_option === '5';
            $events = $log->metadata['events'] ?? [];
            $hasSms = collect($events)->contains(fn ($e) => ($e['event'] ?? '') === 'sms_sent');

            if ($isContactRequest && $notifyContact) {
                $notifications->push([
                    'id' => 'contact-' . $log->id,
                    'type' => 'contact',
                    'title' => 'Personal Contact Request',
                    'subtitle' => 'Caller requested direct callback & recruitment follow-up',
                    'phone' => $log->from_number ?: 'Anonymous Caller',
                    'badge' => 'Urgent Lead',
                    'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/60',
                    'icon_bg' => 'bg-amber-500',
                    'time' => $log->created_at ? $log->created_at->diffForHumans(null, true) : 'just now',
                    'url' => route('admin.ivr.logs.show', $log->id),
                    'is_urgent' => true,
                ]);
                $unreadCount++;
            } elseif ($hasSms && $notifySms) {
                $notifications->push([
                    'id' => 'sms-' . $log->id,
                    'type' => 'sms',
                    'title' => 'SMS Presentation Link Sent',
                    'subtitle' => 'Direct info dispatched to applicant phone',
                    'phone' => $log->from_number ?: 'Mobile Caller',
                    'badge' => 'SMS Sent',
                    'badge_color' => 'bg-purple-50 text-purple-700 border-purple-200/80 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-900/60',
                    'icon_bg' => 'bg-purple-500',
                    'time' => $log->created_at ? $log->created_at->diffForHumans(null, true) : 'just now',
                    'url' => route('admin.ivr.logs.show', $log->id),
                    'is_urgent' => false,
                ]);
            } elseif ($log->status === 'completed' && $notifyInbound) {
                $notifications->push([
                    'id' => 'call-' . $log->id,
                    'type' => 'call',
                    'title' => 'Inbound Call Processed',
                    'subtitle' => "Duration: {$log->formatted_duration} • Option: " . ($log->selected_option ?: 'Menu Navigated'),
                    'phone' => $log->from_number ?: 'Inbound Call',
                    'badge' => 'Completed',
                    'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/60',
                    'icon_bg' => 'bg-emerald-500',
                    'time' => $log->created_at ? $log->created_at->diffForHumans(null, true) : 'just now',
                    'url' => route('admin.ivr.logs.show', $log->id),
                    'is_urgent' => false,
                ]);
            } elseif ($log->status !== 'completed' && $notifyMissed) {
                $notifications->push([
                    'id' => 'missed-' . $log->id,
                    'type' => 'missed',
                    'title' => 'Call Dropped / Abandoned',
                    'subtitle' => 'Caller hung up before choosing an option',
                    'phone' => $log->from_number ?: 'Incomplete Call',
                    'badge' => 'Missed',
                    'badge_color' => 'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900/60',
                    'icon_bg' => 'bg-rose-500',
                    'time' => $log->created_at ? $log->created_at->diffForHumans(null, true) : 'just now',
                    'url' => route('admin.ivr.logs.show', $log->id),
                    'is_urgent' => false,
                ]);
            }
        }
    }
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    hasUnread: {{ $unreadCount > 0 ? 'true' : 'false' }},
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        if (this.dropdownOpen) {
            this.hasUnread = false;
        }
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- Notification Bell Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-10 w-10 sm:h-11 sm:w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
        aria-label="View notifications"
    >
        <!-- Animated Orange Pulse Badge when Unread -->
        @if ($notifyInAppEnabled && $unreadCount > 0)
            <span
                x-show="hasUnread"
                class="absolute right-0 top-0.5 z-1 flex h-2.5 w-2.5 items-center justify-center rounded-full bg-orange-500 ring-2 ring-white dark:ring-gray-900"
            >
                <span class="absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75 animate-ping"></span>
            </span>
        @endif

        <!-- Bell Icon -->
        <svg
            class="fill-current"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
            />
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute ltr:-right-[120px] rtl:-left-[120px] sm:ltr:right-0 sm:rtl:left-0 mt-[17px] flex h-[510px] w-[350px] sm:w-[390px] flex-col rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="text-sm font-bold text-gray-800 dark:text-white">Telephony Notifications</h5>
                @if ($unreadCount > 0)
                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-bold text-orange-700 dark:bg-orange-950/40 dark:text-orange-300 border border-orange-200/80">
                        {{ $unreadCount }} Urgent
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.profile', ['tab' => 'notifications']) }}" title="Configure dynamic notifications"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>
                <button @click="closeDropdown()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition" type="button">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Notification List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar my-2 space-y-1 pr-1">
            @if (!$notifyInAppEnabled)
                <div class="p-6 text-center">
                    <div class="size-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto text-gray-400 mb-2">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">In-App Alerts Disabled</p>
                    <p class="text-[11px] text-gray-400 mt-1">Notifications are currently muted in your notification settings.</p>
                    <a href="{{ route('admin.profile', ['tab' => 'notifications']) }}" class="inline-block mt-3 text-xs font-semibold text-brand-600 hover:underline">
                        Enable in Notification Settings &rarr;
                    </a>
                </div>
            @elseif ($notifications->isEmpty())
                <div class="p-8 text-center">
                    <div class="size-10 rounded-full bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center mx-auto text-emerald-500 mb-2">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">All caught up!</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">No new call activity or lead requests at this time.</p>
                </div>
            @else
                @foreach ($notifications as $item)
                    <a href="{{ $item['url'] }}" @click="closeDropdown()"
                        class="block p-2.5 rounded-xl transition hover:bg-gray-50 dark:hover:bg-white/5 border border-transparent hover:border-gray-100 dark:hover:border-gray-800 {{ $item['is_urgent'] ? 'bg-amber-50/30 dark:bg-amber-950/10' : '' }}">
                        <div class="flex items-start gap-2.5">
                            <!-- Indicator icon dot -->
                            <span class="size-2 rounded-full {{ $item['icon_bg'] }} shrink-0 mt-1.5"></span>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        {{ $item['title'] }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 shrink-0">
                                        {{ $item['time'] }}
                                    </span>
                                </div>

                                <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5">
                                    {{ $item['subtitle'] }}
                                </p>

                                <div class="mt-1.5 flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $item['phone'] }}
                                    </span>
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold border {{ $item['badge_color'] }}">
                                        {{ $item['badge'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>

        <!-- Footer -->
        <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs">
            <a href="{{ route('admin.ivr.logs.index') }}" @click="closeDropdown()"
                class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline">
                View all call logs &rarr;
            </a>

            <a href="{{ route('admin.profile', ['tab' => 'notifications']) }}" @click="closeDropdown()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Notification preferences
            </a>
        </div>
    </div>
</div>
