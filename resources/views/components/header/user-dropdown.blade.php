<div class="relative" x-data="{
    isOpen: false,
    subDropdownOpen: false,
    currentLocale: '{{ app()->getLocale() }}' || localStorage.getItem('locale') || (localStorage.getItem('dir') === 'rtl' ? 'ar' : 'en'),
    languages: [
        {
            id: 'en',
            name: 'English',
            shortName: 'English',
            flag: 'flag-us.svg',
            dir: 'ltr'
        },
        {
            id: 'ar',
            name: 'Arabic (Saudi)',
            shortName: 'Arabic',
            flag: 'flag-sa.svg',
            badge: 'RTL',
            dir: 'rtl'
        },
        {
            id: 'es',
            name: 'Español',
            shortName: 'Español',
            flag: 'flag-es.svg',
            dir: 'ltr'
        },
        {
            id: 'de',
            name: 'Deutsch',
            shortName: 'Deutsch',
            flag: 'flag-de.svg',
            dir: 'ltr'
        }
    ],
    get currentLang() {
        return this.languages.find(l => l.id === this.currentLocale) || this.languages[0];
    },
    toggleDropdown() {
        this.isOpen = !this.isOpen;
        if (!this.isOpen) {
            this.subDropdownOpen = false;
        }
    },
    closeDropdown() {
        this.isOpen = false;
        this.subDropdownOpen = false;
    },
    selectLanguage(lang) {
        this.currentLocale = lang.id;
        const dir = lang.dir || (lang.id === 'ar' ? 'rtl' : 'ltr');
        localStorage.setItem('locale', lang.id);
        localStorage.setItem('dir', dir);
        document.documentElement.setAttribute('dir', dir);
        document.documentElement.setAttribute('lang', lang.id);
        window.location.href = '/locale/' + lang.id;
    }
}" @click.outside="closeDropdown()">
    <!-- User Trigger -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400"
        type="button"
        @click="toggleDropdown()"
    >
        <span class="mr-3 overflow-hidden rounded-full h-10 w-10 rtl:mr-0 rtl:ml-3 flex items-center justify-center bg-gradient-to-tr from-brand-600 to-purple-600 text-white font-bold text-xs shadow-xs">
            {{ auth()->user()->initials ?? 'AD' }}
        </span>

        <span class="block mr-1 font-medium text-theme-sm rtl:mr-0 rtl:ml-1 max-w-[130px] truncate text-start">
            {{ auth()->user()->name ?? 'Administrator' }}
        </span>

        <!-- Chevron Down Icon -->
        <svg
            :class="isOpen ? 'rotate-180' : ''"
            class="transition-transform duration-200 stroke-gray-500 group-hover:stroke-gray-700 dark:stroke-gray-400 dark:group-hover:stroke-gray-200"
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M4.5 6.75L9 11.25L13.5 6.75"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute ltr:right-0 rtl:left-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <!-- User Info -->
        <div class="px-2 pt-1 pb-2">
            <span class="block font-semibold text-gray-800 text-theme-sm dark:text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email ?? 'admin@example.com' }}</span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 pt-3 pb-3 border-y border-gray-200 dark:border-gray-800">
            <li>
                <a
                    href="{{ route('admin.profile', ['tab' => 'profile']) }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-brand-500 dark:group-hover:text-brand-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Edit Profile
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.profile', ['tab' => 'security']) }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-brand-500 dark:group-hover:text-brand-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    Password & Security
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.profile', ['tab' => 'notifications']) }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-brand-500 dark:group-hover:text-brand-400">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                    Notification Settings
                </a>
            </li>

            <!-- Language / RTL Submenu Item -->
            <li class="relative" @click.outside="subDropdownOpen = false">
                <button
                    type="button"
                    @click.stop="subDropdownOpen = !subDropdownOpen"
                    class="group flex max-h-10 w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-theme-sm font-medium transition-colors"
                    :class="subDropdownOpen
                        ? 'bg-gray-100 text-gray-900 dark:bg-white/5 dark:text-white'
                        : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300'"
                >
                    <span class="flex items-center gap-3 text-theme-sm">
                        <svg class="stroke-gray-500 group-hover:stroke-gray-700 dark:stroke-gray-400 dark:group-hover:stroke-gray-300"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.001 2.75C17.1091 2.75 21.2501 6.89178 21.2501 11.9999C21.2501 17.108 17.1091 21.2498 12.001 21.2498M12.001 2.75C6.89289 2.75 2.75195 6.89178 2.75195 11.9999C2.75195 17.108 6.8929 21.2498 12.001 21.2498M12.001 2.75C14.2097 2.75 16.0005 6.8914 16.0005 11.9993C16.0005 17.1073 14.2098 21.2498 12.001 21.2498M12.001 2.75C9.79226 2.75 8.00195 6.89141 8.00195 11.9994C8.00195 17.1073 9.79226 21.2498 12.001 21.2498M3.24561 8.99976H20.7544M3.24561 14.9998H20.7544"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <span>Language</span>
                    </span>

                    <span
                        class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-theme-xs font-medium text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                    >
                        <span x-text="currentLang.shortName"></span>
                        <img :src="'/images/icons/' + currentLang.flag" :alt="currentLang.shortName" class="size-3.5 shrink-0 overflow-hidden rounded-full object-cover" />
                    </span>
                </button>

                <!-- Submenu Flyout -->
                <div x-show="subDropdownOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute top-11 ltr:-left-2 rtl:-right-2 w-[250px] rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg md:top-0 ltr:md:right-[calc(100%+14px)] ltr:md:left-auto rtl:md:left-[calc(100%+14px)] rtl:md:right-auto dark:border-gray-800 dark:bg-gray-dark z-50"
                    style="display: none;"
                >
                    <ul class="flex flex-col gap-1">
                        <template x-for="lang in languages" :key="lang.id">
                            <li>
                                <button
                                    type="button"
                                    @click="selectLanguage(lang)"
                                    class="flex w-full items-center justify-between gap-2 rounded-lg px-2.5 py-2 ltr:text-left rtl:text-right text-theme-sm font-medium transition-colors"
                                    :class="currentLocale === lang.id
                                        ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400'
                                        : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white'"
                                >
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="size-1.5 shrink-0 rounded-full transition-opacity"
                                            :class="currentLocale === lang.id ? 'bg-brand-500 opacity-100 dark:bg-brand-400' : 'opacity-0'"></span>
                                        <img :src="'/images/icons/' + lang.flag" :alt="lang.name" class="size-5 shrink-0 overflow-hidden rounded-full object-cover" />
                                        <span class="truncate" x-text="lang.name"></span>
                                    </span>

                                    <template x-if="lang.badge">
                                        <span
                                            class="rounded bg-warning-50 px-1.5 py-0.5 text-theme-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-400"
                                            x-text="lang.badge">
                                        </span>
                                    </template>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </li>
        </ul>

        <!-- Sign Out Form -->
        <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </span>
                Sign out
            </button>
        </form>
    </div>
</div>
