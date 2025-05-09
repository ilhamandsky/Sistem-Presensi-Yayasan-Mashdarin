<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-0 px-0 sm:px-0 lg:px-0">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0 mr-6 ml-3 sm:ml-7">
                    <a href="{{ Auth::user()->isAdmin ? route('admin.dashboard') : route('home') }}" class="mr-4">
                        <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-18 h-14 rounded-full" />
                    </a>
                    <h2 class="text-xl font-bold ml-1">
                        <span class="text-amber-500">Si</span><span class="text-blue-900">Mash</span>
                    </h2>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-6 sm:flex md:ms-10 md:space-x-5 lg:space-x-8">
                    @if (Auth::user()->isAdmin)
                        <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Home') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('admin.barcodes') }}" :active="request()->routeIs('admin.barcodes')">
                            {{ __('QR Code') }}
                        </x-nav-link>
                        <x-nav-link class="hidden md:inline-flex" href="{{ route('home') }}" :active="request()->routeIs('home')">
                            {{ __('Scan') }}
                        </x-nav-link>
                        <x-nav-link class="hidden md:inline-flex" href="{{ route('admin.employees') }}"
                            :active="request()->routeIs('admin.employees')">
                            {{ __('Karyawan') }}
                        </x-nav-link>

                        <!-- Dropdown Master Data -->
                        <x-nav-dropdown :active="request()->routeIs('admin.masters.*')" triggerClasses="text-nowrap">
                            <x-slot name="trigger">
                                {{ __('Master Data') }}
                                <x-heroicon-o-chevron-down class="ms-2 h-5 w-5 text-gray-400" />
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('admin.masters.job-title') }}" :active="request()->routeIs('admin.masters.job-title')">
                                    {{ __('Jabatan') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.masters.shift') }}" :active="request()->routeIs('admin.masters.shift')">
                                    {{ __('Shift') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('admin.attendances') }}"
                            :active="request()->routeIs('admin.attendances')">
                            {{ __('Laporan Presensi') }}
                        </x-nav-link>
                    @else
                        <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                            {{ __('Beranda') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex gap-2">
                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <!-- Settings Dropdown -->
                    <div class="relative ms-3">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:bg-gray-50 focus:outline-none active:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300 dark:focus:bg-gray-700 dark:active:bg-gray-700">
                                        {{ Auth::user()->name }}
                                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('profile.show') }}">
                                    {{ __('Kelola Profil') }}
                                </x-dropdown-link>

                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                        {{ __('API Tokens') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="border-t border-gray-200 dark:border-gray-600"></div>

                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf
                                    <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="me-2 flex items-center sm:hidden">
                    <button @click="open = !open"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            @if (Auth::user()->isAdmin)
                <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Beranda') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.barcodes') }}" :active="request()->routeIs('admin.barcodes')">
                    {{ __('QR Code') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                    {{ __('Scan') }}
                </x-responsive-nav-link>

                <!-- Dropdown for Mobile -->
                <div x-data="{ openMaster: false }">
                    <x-responsive-nav-link @click="openMaster = !openMaster" :active="request()->routeIs('admin.masters.*')"
                        class="flex items-center justify-between">
                        <span>{{ __('Master Data') }}</span>

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="ms-2 h-5 w-5 text-gray-400 transition-transform duration-200" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                            x-bind:class="{ 'rotate-180': openMaster }">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </x-responsive-nav-link>
                    <div x-show="openMaster" class="ms-4 mt-1 space-y-1">
                        <x-responsive-nav-link href="{{ route('admin.masters.job-title') }}" :active="request()->routeIs('admin.masters.job-title')">
                            {{ __('Jabatan') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('admin.masters.shift') }}" :active="request()->routeIs('admin.masters.shift')">
                            {{ __('Shift') }}
                        </x-responsive-nav-link>
                    </div>
                </div>

                <x-responsive-nav-link href="{{ route('admin.employees') }}" :active="request()->routeIs('admin.employees')">
                    {{ __('Karyawan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.attendances') }}" :active="request()->routeIs('admin.attendances')">
                    {{ __('Laporan Presensi') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                    {{ __('Beranda') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings -->
        <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
            <div class="flex items-center px-4">
                <div>
                    <div class="text-base font-medium text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
