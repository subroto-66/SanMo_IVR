@extends('layouts.app')

@section('content')
    <div class="max-w-5xl space-y-6" x-data="{
        activeTab: '{{ old('tab', $currentTab ?? 'profile') }}',
        showCurrentPass: false,
        showNewPass: false,
        showConfirmPass: false
    }">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                    Profile & Account Center
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage your administrator credentials, security access, and dynamic telephony notification channels.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800">
                    <span class="size-2 rounded-full bg-brand-500"></span>
                    Account Security & Settings
                </span>
            </div>
        </div>

        <!-- Success & Error Banners -->
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
                    Please resolve the following:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs font-normal">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Overview Card -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <!-- Initials Avatar -->
                <div class="relative shrink-0">
                    <div class="size-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-purple-600 flex items-center justify-center text-white text-2xl font-black shadow-theme-sm border-2 border-white dark:border-gray-800">
                        {{ $user->initials }}
                    </div>
                    <span class="absolute -bottom-1 -end-1 size-4.5 rounded-full bg-emerald-500 border-2 border-white dark:border-gray-900" title="Active"></span>
                </div>

                <!-- User Details -->
                <div class="flex-1 text-center sm:text-start">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h2>
                        <span class="inline-flex items-center gap-1 rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 w-fit mx-auto sm:mx-0">
                            {{ $user->job_title ?? 'System Administrator' }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                        <span class="inline-flex items-center gap-1">
                            <svg class="size-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->email }}
                        </span>
                        @if ($user->phone)
                            <span class="inline-flex items-center gap-1">
                                <svg class="size-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $user->phone }}
                            </span>
                        @endif
                    </p>

                    @if ($user->bio)
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 max-w-2xl">
                            {{ $user->bio }}
                        </p>
                    @endif
                </div>

                <!-- Live Notification Counter -->
                <div class="shrink-0 flex items-center gap-2">
                    <button type="button" @click="activeTab = 'notifications'"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50/80 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        <svg class="size-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>Alerts Configured</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex gap-2 sm:gap-6 overflow-x-auto no-scrollbar" aria-label="Tabs">
                <!-- Tab 1: Profile Information -->
                <button type="button" @click="activeTab = 'profile'"
                    :class="activeTab === 'profile'
                        ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="inline-flex items-center gap-2.5 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-semibold transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile Details</span>
                </button>

                <!-- Tab 2: Security & Password -->
                <button type="button" @click="activeTab = 'security'"
                    :class="activeTab === 'security'
                        ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="inline-flex items-center gap-2.5 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-semibold transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Security & Password</span>
                </button>

                <!-- Tab 3: Dynamic Notification Settings -->
                <button type="button" @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications'
                        ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="inline-flex items-center gap-2.5 whitespace-nowrap border-b-2 py-3 px-3 text-xs sm:text-sm font-semibold transition">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>Dynamic Notification Settings</span>
                </button>
            </nav>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: PROFILE EDITING                     -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'profile'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Personal Information</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Update your administrator profile, official email, and contact details used for telephony system alerts.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="profile">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <p class="text-[11px] text-gray-400 mt-1">Used for signing in and receiving critical telephony lead notifications.</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Phone Number
                            </label>
                            <input type="text" name="phone" placeholder="+1 (555) 234-5678" value="{{ old('phone', $user->phone) }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <p class="text-[11px] text-gray-400 mt-1">E.164 format recommended (e.g. +14155552671) for instant SMS alerts.</p>
                        </div>

                        <!-- Job Title -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Job Title / Role
                            </label>
                            <input type="text" name="job_title" placeholder="System Administrator" value="{{ old('job_title', $user->job_title) }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                        </div>

                        <!-- Bio -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                Administrative Notes / Bio
                            </label>
                            <textarea name="bio" rows="3" placeholder="Operations Manager overseeing FlowCore inbound recruitment IVR line..."
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600 focus:ring-2 focus:ring-brand-500/30">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: PASSWORD CHANGE                     -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'security'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Change Account Password</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Ensure your administrative account is secured with a strong, unpredictable password.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-5 max-w-xl">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="security">

                    <!-- Current Password -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showCurrentPass ? 'text' : 'password'" name="current_password" required
                                placeholder="Enter your current password"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 ltr:pr-11 rtl:pl-11 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <button type="button" @click="showCurrentPass = !showCurrentPass"
                                class="absolute top-1/2 -translate-y-1/2 ltr:right-3 rtl:left-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg x-show="!showCurrentPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showCurrentPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showNewPass ? 'text' : 'password'" name="password" required minlength="8"
                                placeholder="Enter a secure new password (min. 8 characters)"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 ltr:pr-11 rtl:pl-11 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <button type="button" @click="showNewPass = !showNewPass"
                                class="absolute top-1/2 -translate-y-1/2 ltr:right-3 rtl:left-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg x-show="!showNewPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showNewPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                                placeholder="Re-enter your new password to verify"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 ltr:pr-11 rtl:pl-11 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                            <button type="button" @click="showConfirmPass = !showConfirmPass"
                                class="absolute top-1/2 -translate-y-1/2 ltr:right-3 rtl:left-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg x-show="!showConfirmPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showConfirmPass" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Security Guidance -->
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <div class="font-semibold text-gray-700 dark:text-gray-200">Password Requirements:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                            <li>Minimum 8 characters in length.</li>
                            <li>Include uppercase & lowercase letters and digits for enhanced security.</li>
                            <li>Cannot match your existing current password.</li>
                        </ul>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600 focus:ring-2 focus:ring-brand-500/30">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: DYNAMIC NOTIFICATION SETTINGS       -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'notifications'" x-cloak class="space-y-6">
            <form method="POST" action="{{ route('admin.profile.notifications') }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="notifications">

                <!-- 1. Header In-App Notifications -->
                <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-brand-500"></span>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">1. In-App Notification Bell & Live Alerts</h2>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Controls which real-time telephony and recruitment events trigger the unread badge and dropdown in the top header.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Master In-App Toggle -->
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-brand-100 bg-brand-50/20 dark:border-brand-900/30 dark:bg-brand-950/20">
                            <div>
                                <label class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">
                                    Enable In-App Header Notifications
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Show animated notification bell badge and real-time IVR event feed.
                                </p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="notify_in_app_enabled" value="1"
                                    class="peer sr-only" {{ $settings['notify_in_app_enabled'] ? 'checked' : '' }} />
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700 dark:border-gray-600"></div>
                            </label>
                        </div>

                        <!-- Specific Event Checkboxes -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                            <!-- Contact Requests (Option 5) -->
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                <input type="checkbox" name="notify_in_app_contact_requests" value="1"
                                    class="mt-0.5 rounded text-brand-500 focus:ring-brand-500/20"
                                    {{ $settings['notify_in_app_contact_requests'] ? 'checked' : '' }} />
                                <div>
                                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">Personal Contact Requests (Option 5)</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Flag urgent callback requests from inbound candidates as high-priority leads.</span>
                                </div>
                            </label>

                            <!-- Inbound Calls -->
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                <input type="checkbox" name="notify_in_app_inbound_calls" value="1"
                                    class="mt-0.5 rounded text-brand-500 focus:ring-brand-500/20"
                                    {{ $settings['notify_in_app_inbound_calls'] ? 'checked' : '' }} />
                                <div>
                                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">All Inbound Telephone Calls</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Display every connected caller with duration and timestamps in feed.</span>
                                </div>
                            </label>

                            <!-- SMS Activity -->
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                <input type="checkbox" name="notify_in_app_sms_activity" value="1"
                                    class="mt-0.5 rounded text-brand-500 focus:ring-brand-500/20"
                                    {{ $settings['notify_in_app_sms_activity'] ? 'checked' : '' }} />
                                <div>
                                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">SMS Link Dispatches</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Show notification whenever candidates receive FlowCore presentation or info links.</span>
                                </div>
                            </label>

                            <!-- Missed Calls -->
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                <input type="checkbox" name="notify_in_app_missed_calls" value="1"
                                    class="mt-0.5 rounded text-brand-500 focus:ring-brand-500/20"
                                    {{ $settings['notify_in_app_missed_calls'] ? 'checked' : '' }} />
                                <div>
                                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">Abandoned / Missed Calls</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Alert staff if a caller hangs up without selecting an option.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Staff SMS Telephony Alerts -->
                <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-purple-500"></span>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">2. Direct SMS Notifications to Staff Phone</h2>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Immediately forward caller contact requests to an on-duty recruiter or administrator mobile phone via Twilio SMS.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Master SMS Toggle -->
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-purple-100 bg-purple-50/20 dark:border-purple-900/30 dark:bg-purple-950/20">
                            <div>
                                <label class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">
                                    Enable Direct SMS Forwarding to Staff
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Dispatches SMS alert directly to staff phone whenever an urgent event occurs.
                                </p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="notify_sms_enabled" value="1"
                                    class="peer sr-only" {{ $settings['notify_sms_enabled'] ? 'checked' : '' }} />
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-600 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700 dark:border-gray-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Recipient Phone Number -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Staff Alert Mobile Number
                                </label>
                                <input type="text" name="notify_sms_phone" placeholder="+1 (555) 789-0123"
                                    value="{{ old('notify_sms_phone', $settings['notify_sms_phone']) }}"
                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition font-mono" />
                                <p class="text-[11px] text-gray-400 mt-1">E.164 format with country code (e.g. +14155552671).</p>
                            </div>

                            <!-- Contact Request Trigger -->
                            <div class="flex items-center">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer w-full">
                                    <input type="checkbox" name="notify_sms_on_contact_request" value="1"
                                        class="mt-0.5 rounded text-purple-600 focus:ring-purple-500/20"
                                        {{ $settings['notify_sms_on_contact_request'] ? 'checked' : '' }} />
                                    <div>
                                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">Instant SMS on Option 5 (Contact Request)</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Sends text with the applicant's phone number as soon as they select Option 5.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Email Lead & Daily Digest Notifications -->
                <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">3. Email Notifications & Recruitment Inquiries</h2>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Forward caller information, contact inquiries, and periodic digests to a corporate recruitment inbox.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Master Email Toggle -->
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/20 dark:border-emerald-900/30 dark:bg-emerald-950/20">
                            <div>
                                <label class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">
                                    Enable Email Lead Forwarding
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Send email notifications for high priority telephony activities.
                                </p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="notify_email_enabled" value="1"
                                    class="peer sr-only" {{ $settings['notify_email_enabled'] ? 'checked' : '' }} />
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700 dark:border-gray-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Destination Email -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Recipient Alert Email
                                </label>
                                <input type="email" name="notify_email_address" placeholder="recruitment@flowcore.com"
                                    value="{{ old('notify_email_address', $settings['notify_email_address']) }}"
                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition" />
                                <p class="text-[11px] text-gray-400 mt-1">Leaves default to your login email address if empty.</p>
                            </div>

                            <!-- Email Options -->
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                    <input type="checkbox" name="notify_email_contact_requests" value="1"
                                        class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500/20"
                                        {{ $settings['notify_email_contact_requests'] ? 'checked' : '' }} />
                                    <div>
                                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">Email on Contact Request (Option 5)</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Immediate email dispatch when candidate asks for follow-up.</span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200/70 hover:bg-gray-50/70 dark:border-gray-800 dark:hover:bg-gray-800/40 transition cursor-pointer">
                                    <input type="checkbox" name="notify_email_daily_digest" value="1"
                                        class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500/20"
                                        {{ $settings['notify_email_daily_digest'] ? 'checked' : '' }} />
                                    <div>
                                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 block">Daily Call Activity Summary</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Receive morning digest of calls and SMS links dispatched.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Sound & Browser Audio Alerts -->
                <div class="rounded-2xl border border-gray-200/80 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Audible Notification Chime</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Play an audible chime in your dashboard browser tab when new caller requests arrive.
                            </p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="notify_sound_enabled" value="1"
                                class="peer sr-only" {{ $settings['notify_sound_enabled'] ? 'checked' : '' }} />
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:bg-gray-700 dark:border-gray-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <!-- Send Test SMS Button Form -->
                    <div>
                        @if (!empty($settings['notify_sms_phone']))
                            <button type="button"
                                onclick="document.getElementById('test-sms-form').submit()"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                <svg class="size-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Send Test SMS Alert to {{ $settings['notify_sms_phone'] }}
                            </button>
                        @endif
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-600 focus:ring-2 focus:ring-brand-500/30">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Notification Preferences
                    </button>
                </div>
            </form>

            <!-- Separate Hidden Form for Testing SMS -->
            <form id="test-sms-form" method="POST" action="{{ route('admin.profile.notifications.test') }}" class="hidden">
                @csrf
            </form>
        </div>
    </div>
@endsection
