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
    @endpushOnce
    <h3 class="col-span-2 mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Data Presensi
    </h3>
    <div class="mb-1 text-sm dark:text-white">Filter:</div>
    <div class="mb-4 grid grid-cols-2 flex-wrap items-center gap-5 md:gap-8 lg:flex">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <x-label for="month_filter" value="Per Bulan"></x-label>
            <x-input type="month" name="month_filter" id="month_filter" wire:model.live="month" />
        </div>

        <div class="col-span-2 flex flex-col gap-3 lg:flex-row lg:items-center">
            <x-label for="day_filter" value="Per Hari"></x-label>
            <x-input type="date" name="day_filter" id="day_filter" wire:model.live="date" />
        </div>
        <x-select id="jobTitle" wire:model.live="jobTitle">
            <option value="">{{ __('Select Job Title') }}</option>
            @foreach (App\Models\JobTitle::all() as $_jobTitle)
                <option value="{{ $_jobTitle->id }}" {{ $_jobTitle->id == $jobTitle ? 'selected' : '' }}>
                    {{ $_jobTitle->name }}
                </option>
            @endforeach
        </x-select>
        <div class="col-span-2 flex items-center gap-2 lg:w-96">
            <x-input type="text" class="w-full" name="search" id="seacrh" wire:model="search"
                placeholder="{{ __('Search') }}" />
            <x-button type="button" wire:click="$refresh" wire:loading.attr="disabled">{{ __('Search') }}</x-button>
            @if ($search)
                <x-secondary-button type="button" wire:click="$set('search', '')" wire:loading.attr="disabled">
                    {{ __('Reset') }}
                </x-secondary-button>
            @endif
        </div>
        <div class="lg:hidden"></div>
        <x-secondary-button
            href="{{ route('admin.attendances.report', ['month' => $month, 'week' => $week, 'date' => $date, 'jobTitle' => $jobTitle]) }}"
            class="flex justify-center gap-2">
            Cetak Laporan
            <x-heroicon-o-printer class="h-5 w-5" />
        </x-secondary-button>
    </div>
    <div class="overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}
                    </th>
                    @if ($showUserDetail)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Job Title') }}
                        </th>
                        @if (!$isPerDayFilter)
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                                {{ __('Status') }}
                            </th>
                        @endif
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time In') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
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
                            class="{{ $textClass }} text-nowrap px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
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
                    <tr wire:key="{{ $employee->id }}" class="group">
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
                                            class="px-2 py-1 rounded bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200">Hadir</span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200">Tidak
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
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700';
                                    } else {
                                        $displayStatus = 'Tidak Hadir';
                                        $bgColor = 'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700';
                                    }

                                    // Jika akhir pekan atau tanggal di masa depan dan tidak ada absensi
                                    if (($isWeekend || !$date->isPast()) && !$attendance) {
                                        $displayStatus = '-';
                                        $bgColor = 'hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600';
                                    }
                                } else {
                                    // Untuk filter per bulan: gunakan simbol
                                    if ($isPresent) {
                                        $displayStatus = '✅';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $presentCount++;
                                    } else {
                                        $displayStatus = '❌';
                                        $bgColor =
                                            'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                        if (!$isWeekend && $date->isPast()) {
                                            $absentCount++;
                                        }
                                    }

                                    // Jika akhir pekan atau tanggal di masa depan dan tidak ada absensi
                                    if (($isWeekend || !$date->isPast()) && !$attendance) {
                                        $displayStatus = '-';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                    }
                                }
                            @endphp
                            @if (!$isPerDayFilter && $attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
                            @else
                                <td
                                    class="{{ $bgColor }} text-nowrap cursor-pointer px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $displayStatus }}
                                </td>
                            @endif
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
</div>
