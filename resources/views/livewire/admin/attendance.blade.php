@php
    use Illuminate\Support\Carbon;
    $m = Carbon::parse($month);
    $showUserDetail = !$month || $week || $date; // is week or day filter
    $isPerDayFilter = isset($date);
@endphp
<div>
    @pushOnce('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .refresh-btn {
                transition: transform 0.5s ease;
            }

            .refresh-btn.rotating {
                transform: rotate(360deg);
            }
        </style>
    @endpushOnce

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Data Presensi
        </h3>
        <!-- Tombol Refresh -->
        <button wire:click="clearAttendanceCache" wire:loading.class="rotating"
            class="refresh-btn bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md flex items-center gap-2 transition-colors duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span wire:loading.remove wire:target="clearAttendanceCache">Perbarui Data</span>
            <span wire:loading wire:target="clearAttendanceCache">Memuat...</span>
        </button>
    </div>

    <div class="mb-1 text-sm dark:text-white">Filter:</div>
    <div class="mb-4">
        <!-- Filter Perbulan dan Perhari (Baris Pertama) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div class="flex flex-col gap-3">
                <x-label for="month_filter" value="Per Bulan"></x-label>
                <x-input type="month" name="month_filter" id="month_filter" wire:model.live="month" class="w-full" />
            </div>

            <div class="flex flex-col gap-3">
                <x-label for="day_filter" value="Per Hari"></x-label>
                <x-input type="date" name="day_filter" id="day_filter" wire:model.live="date" class="w-full" />
            </div>
        </div>

        <!-- Filter Jabatan dan Pencarian (Baris Kedua) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div class="flex flex-col gap-3">

                <x-select id="jobTitle" wire:model.live="jobTitle" class="w-full">
                    <option value="">{{ __('Select Job Title') }}</option>
                    @foreach (App\Models\JobTitle::all() as $_jobTitle)
                        <option value="{{ $_jobTitle->id }}" {{ $_jobTitle->id == $jobTitle ? 'selected' : '' }}>
                            {{ $_jobTitle->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex flex-col gap-3">

                <div class="flex items-center gap-2">
                    <x-input type="text" class="w-full" name="search" id="search" wire:model="search"
                        placeholder="{{ __('Cari Nama') }}" />
                    <x-button type="button" wire:click="$refresh"
                        wire:loading.attr="disabled">{{ __('Cari') }}</x-button>
                    @if ($search)
                        <x-secondary-button type="button" wire:click="$set('search', '')" wire:loading.attr="disabled">
                            {{ __('Reset') }}
                        </x-secondary-button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tombol Cetak Laporan -->
        <div class="flex justify-end">
            <x-secondary-button
                href="{{ route('admin.attendances.report', ['month' => $month, 'week' => $week, 'date' => $date, 'jobTitle' => $jobTitle]) }}"
                class="flex justify-center gap-2">
                Cetak Laporan
                <x-heroicon-o-printer class="h-5 w-5" />
            </x-secondary-button>
        </div>
    </div>

    <!-- Notifikasi status refresh -->
    <div id="notification-banner" x-data="{ show: false, message: '' }" x-show="show" x-init="Livewire.on('banner-message', (msg) => {
        message = msg;
        show = true;
        setTimeout(() => { show = false }, 3000);
    })"
        class="mb-4 p-3 bg-green-100 text-green-800 rounded-md flex items-center justify-between"
        style="display: none;">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="message">Data berhasil disegarkan</span>
        </div>
        <button @click="show = false" class="text-green-600 hover:text-green-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="overflow-x-scroll">
        <table class="w-full overflow-hidden rounded-t-lg">
            <thead style="background-color:rgb(176, 201, 251);">
                <tr>
                    <th scope="col"
                        class="px-4 py-3 text-left text-xs font-medium text-black-500 dark:text-gray-300">
                        {{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}
                    </th>
                    @if ($showUserDetail)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-black-500 dark:text-gray-300">
                            {{ __('Job Title') }}
                        </th>
                        @if (!$isPerDayFilter)
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-black-500 dark:text-gray-300">
                                {{ __('Status') }}
                            </th>
                        @endif
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-black-500 dark:text-gray-300">
                            {{ __('Time In') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-black-500 dark:text-gray-300">
                            {{ __('Time Out') }}
                        </th>
                    @endif
                    @foreach ($dates as $date)
                        @php
                            if (!$isPerDayFilter && $date->isSunday()) {
                                // Minggu merah
                                $textClass = 'text-red-500 dark:text-red-300';
                            } elseif (!$isPerDayFilter && $date->isFriday()) {
                                // Jumat hijau
                                $textClass = 'text-green-500 dark:text-green-300';
                            } else {
                                $textClass = 'text-gray-500 dark:text-gray-300';
                            }
                        @endphp
                        <th scope="col"
                            class="{{ $textClass }} text-nowrap px-1 py-3 text-center text-xs font-medium text-black dark:text-gray-300">
                            @if ($isPerDayFilter)
                                Status
                            @else
                                {{ $date->format('d/m') }}
                            @endif
                        </th>
                    @endforeach
                    @if (!$isPerDayFilter)
                        <th scope="col"
                            class="text-nowrap px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
                            Hadir
                        </th>
                        <th scope="col"
                            class="text-nowrap px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
                            Tidak Hadir
                        </th>
                    @endif
                    @if ($isPerDayFilter)
                        <th scope="col" class="relative">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'cursor-pointer px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
                @endphp
                @foreach ($employees as $employee)
                    @php
                        $attendances = $employee->attendances;
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="hover:bg-gray-50">
                        {{-- Detail karyawan --}}
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        @if ($showUserDetail)
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->jobTitle?->name ?? '-' }}
                            </td>
                            @php
                                $attendance = $employee->attendances->isEmpty()
                                    ? null
                                    : $employee->attendances->first();
                                $timeIn = $attendance ? $attendance['time_in'] : null;
                                $timeOut = $attendance ? $attendance['time_out'] : null;
                                $isPresent =
                                    $attendance &&
                                    ($attendance['status'] == 'present' || $attendance['status'] == 'late');
                            @endphp
                            @if (!$isPerDayFilter)
                                <td
                                    class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                    @if ($isPresent)
                                        <span
                                            class="inline-block whitespace-nowrap text-xs text-green-600 px-2 py-1 rounded-full font-medium bg-green-100">Hadir</span>
                                    @else
                                        <span
                                            class="inline-block whitespace-nowrap text-xs text-red-500 px-2 py-1 rounded-full font-medium bg-red-100">Tidak
                                            Hadir</span>
                                    @endif
                                </td>
                            @endif
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeIn ?? '-' }}
                            </td>
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeOut ?? '-' }}
                            </td>
                        @endif

                        {{-- Absensi --}}
                        @php
                            $presentCount = 0;
                            $absentCount = 0;
                        @endphp
                        @foreach ($dates as $date)
                            @php
                                $isWeekend = $date->isWeekend();
                                $attendance = $attendances->firstWhere(
                                    fn($v, $k) => $v['date'] === $date->format('Y-m-d'),
                                );
                                $isPresent =
                                    $attendance &&
                                    ($attendance['status'] == 'present' || $attendance['status'] == 'late');

                                if ($isPerDayFilter) {
                                    // Untuk filter per hari: gunakan kata-kata
                                    if ($isPresent) {
                                        $displayStatus = 'Hadir';
                                        $statusClass =
                                            'inline-block whitespace-nowrap text-xs text-green-600 px-2 py-1 rounded-full font-medium bg-green-100';
                                    } else {
                                        $displayStatus = 'Tidak Hadir';
                                        $statusClass =
                                            'inline-block whitespace-nowrap text-xs text-red-500 px-2 py-1 rounded-full font-medium bg-red-100';
                                    }

                                    if (($isWeekend || !$date->isPast()) && !$attendance) {
                                        $displayStatus = '-';
                                        $statusClass = 'text-gray-500 dark:text-gray-300';
                                    }

                                    $bgColor = '';
                                    $textClass = '';
                                } else {
                                    // Untuk filter per bulan: gunakan simbol
                                    if ($isPresent) {
                                        $displayStatus = '✅';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $textClass = 'text-green-800 dark:text-green-200';
                                        $presentCount++;
                                    } else {
                                        $displayStatus = '❌';
                                        $bgColor =
                                            'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                        $textClass = 'text-red-800 dark:text-red-200';
                                        if (!$isWeekend && $date->isPast()) {
                                            $absentCount++;
                                        }
                                    }

                                    // Jika akhir pekan atau tanggal di masa depan dan tidak ada absensi
                                    if (($isWeekend || !$date->isPast()) && !$attendance) {
                                        $displayStatus = '-';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        $textClass = 'text-gray-500 dark:text-gray-300';
                                    }
                                }
                            @endphp

                            <td
                                class="{{ $bgColor }} {{ $textClass }} cursor-pointer px-1 py-3 text-center text-sm font-medium">
                                @if ($isPerDayFilter && $displayStatus != '-')
                                    <span class="{{ $statusClass }}">
                                        {{ $displayStatus }}
                                    </span>
                                @else
                                    {{ $displayStatus }}
                                @endif
                            </td>
                        @endforeach

                        {{-- Total --}}
                        @if (!$isPerDayFilter)
                            <td
                                class="cursor-pointer border border-gray-300 px-1 py-3 text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:group-hover:bg-gray-700">
                                {{ $presentCount }}
                            </td>
                            <td
                                class="cursor-pointer border border-gray-300 px-1 py-3 text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:group-hover:bg-gray-700">
                                {{ $absentCount }}
                            </td>
                        @endif

                        {{-- Action --}}
                        @if ($isPerDayFilter)
                            @php
                                $attendance = $employee->attendances->isEmpty()
                                    ? null
                                    : $employee->attendances->first();
                            @endphp
                            <td
                                class="cursor-pointer text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:text-white dark:group-hover:bg-gray-700">

                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($employees->isEmpty())
        <div class="my-2 text-center text-sm font-medium text-gray-900 dark:text-gray-100">
            Tidak ada data
        </div>
    @endif
    <div class="mt-3">
        {{ $employees->links() }}
    </div>

    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')

    @pushOnce('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Event listener untuk banner message dari Livewire
                Livewire.on('banner-message', function(message) {
                    // Jika menggunakan AlpineJS, ini sudah ditangani di atas
                    // Jika tidak, tambahkan kode manual di sini
                });

                // Tambahkan animasi untuk tombol refresh
                const refreshBtn = document.querySelector('.refresh-btn');
                if (refreshBtn) {
                    refreshBtn.addEventListener('click', function() {
                        this.classList.add('rotating');
                        setTimeout(() => {
                            this.classList.remove('rotating');
                        }, 1000);
                    });
                }
            });
        </script>
    @endpushOnce
</div>
