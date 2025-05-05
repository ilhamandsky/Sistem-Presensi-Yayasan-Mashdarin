@php
$date = Carbon\Carbon::now();
@endphp

{{-- Root element tunggal untuk Livewire --}}
<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Karyawan --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Karyawan</div>
                <div class="text-xl font-bold text-gray-800">{{ $employeesCount }}</div>
            </div>
        </div>

        {{-- Jabatan --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-briefcase text-yellow-600 text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Jabatan</div>
                <div class="text-xl font-bold text-gray-800">{{ $jobTitleCount }}</div>
            </div>
        </div>

        {{-- Shift --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-clock text-green-600 text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Shift</div>
                <div class="text-xl font-bold text-gray-800">{{ $shiftCount }}</div>
            </div>
        </div>
    </div>

    {{-- Kotak putih besar untuk presensi --}}
    <div class="bg-white p-6 rounded-xl shadow">
        {{-- Header Presensi --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Presensi Hari Ini</h3>
            <div class="text-sm text-gray-600">
                Hari ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>
        </div>

        {{-- Statistik Hadir / Tidak Hadir --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-green-100 text-center p-4 rounded-lg">
                <div class="text-lg font-bold">Hadir</div>
                <div class="text-2xl">{{ $presentCount }}</div>
            </div>
            <div class="bg-red-100 text-center p-4 rounded-lg">
                <div class="text-lg font-bold">Tidak Hadir</div>
                <div class="text-2xl">{{ $absentCount }}</div>
            </div>
        </div>

        <div class="mb-4 overflow-x-scroll">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Name') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Job Title') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Shift') }}
                        </th>
                        <th
                            class="text-nowrap px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
                            Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time In') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time Out') }}
                        </th>
                        <th class="relative"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @php
                    $class = 'px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
                    @endphp
                    @foreach ($employees as $employee)
                    @php
                    $attendance = $employee->attendance;
                    $timeIn = $attendance?->time_in?->format('H:i');
                    $timeOut = $attendance?->time_out?->format('H:i');
                    $isWeekend = $date->isWeekend();
                    $isPresent = $attendance && in_array($attendance->status, ['present', 'late']);

                    if ($isPresent) {
                    $displayStatus = 'Hadir';
                    $bgColor = 'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700';
                    } else {
                    $displayStatus = 'Tidak Hadir';
                    $bgColor = 'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700';
                    }

                    if (($isWeekend || !$date->isPast()) && !$attendance) {
                    $displayStatus = '-';
                    $bgColor = 'hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600';
                    }
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="group">
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->jobTitle?->name ?? '-' }}
                        </td>
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $attendance->shift?->name ?? '-' }}
                        </td>
                        <td class="{{ $class }} text-center">
                            @if ($displayStatus === 'Hadir')
                            <span class="inline-block px-3 py-1 text-sm rounded-full bg-[#e4fbee] text-[#3cb371] font-semibold">
                                Hadir
                            </span>
                            @elseif ($displayStatus === 'Tidak Hadir')
                            <span class="inline-block px-3 py-1 text-sm rounded-full bg-[#fdeaea] text-[#e74c3c] font-semibold">
                                Tidak Hadir
                            </span>
                            @else
                            <span class="inline-block px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-500 font-semibold">
                                -
                            </span>
                            @endif
                        </td>
                        <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $timeIn ?? '-' }}
                        </td>
                        <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $timeOut ?? '-' }}
                        </td>
                        <td class="sr-only"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $employees->links() }}

        <x-attendance-detail-modal :current-attendance="$currentAttendance" />
        @stack('attendance-detail-scripts')
    </div>
</div>