@php
    $date = Carbon\Carbon::now();
@endphp

<div class="bg-grey p-6 rounded-xl">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Karyawan --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="p-3 rounded-full bg-indigo-100">
                <i class="fas fa-users text-xl text-indigo-600"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Karyawan</div>
                <div class="text-xl font-bold text-gray-800">{{ $employeesCount }}</div>
            </div>
        </div>

        {{-- Jabatan --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="p-3 rounded-full bg-yellow-100">
                <i class="fas fa-briefcase text-xl text-yellow-600"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Jabatan</div>
                <div class="text-xl font-bold text-gray-800">{{ $jobTitleCount }}</div>
            </div>
        </div>

        {{-- Shift --}}
        <div class="flex items-center gap-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="p-3 rounded-full bg-green-100">
                <i class="fas fa-clock text-xl text-green-600"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Jumlah Shift</div>
                <div class="text-xl font-bold text-gray-800">{{ $shiftCount }}</div>
            </div>
        </div>
    </div>

    {{-- Kotak putih besar untuk presensi --}}
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm">
        {{-- Header Presensi --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
            <h3 class="text-xl font-bold mb-2 md:mb-0">Presensi Hari Ini</h3>
            <div class="text-sm text-gray-600">
                Hari ini: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>
        </div>

        {{-- Statistik Hadir / Tidak Hadir --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-green-100 text-center p-3 rounded-lg">
                <div class="text-sm sm:text-lg font-bold text-green-800">Hadir</div>
                <div class="text-xl sm:text-2xl font-bold text-green-700">{{ $presentCount }}</div>
            </div>
            <div class="bg-red-100 text-center p-3 rounded-lg">
                <div class="text-sm sm:text-lg font-bold text-red-800">Tidak Hadir</div>
                <div class="text-xl sm:text-2xl font-bold text-red-700">{{ $absentCount }}</div>
            </div>
        </div>

        <div class="mb-4 overflow-x-auto w-full" style="overflow-x: auto;">
            <table class="w-full overflow-hidden rounded-t-lg">
                <thead style="background-color:rgb(176, 201, 251);">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 whitespace-nowrap">
                            {{ __('Name') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 whitespace-nowrap">
                            {{ __('Job Title') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 whitespace-nowrap">
                            {{ __('Shift') }}
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 whitespace-nowrap">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 whitespace-nowrap">
                            {{ __('Time In') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 whitespace-nowrap">
                            {{ __('Time Out') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($employees as $employee)
                        @php
                            $attendance = $employee->attendance;
                            $timeIn = $attendance?->time_in?->format('H:i');
                            $timeOut = $attendance?->time_out?->format('H:i');
                            $isWeekend = $date->isWeekend();
                            $isPresent = $attendance && in_array($attendance->status, ['present', 'late']);

                            if ($isPresent) {
                                $displayStatus = 'Hadir';
                                $statusClass =
                                    'inline-block whitespace-nowrap w-full text-xs text-green-600 px-2 py-1 rounded-full font-medium bg-green-100';
                            } else {
                                $displayStatus = 'Tidak Hadir';
                                $statusClass =
                                    'inline-block whitespace-nowrap w-full text-xs text-red-500 px-2 py-1 rounded-full font-medium bg-red-100';
                            }

                            if (($isWeekend || !$date->isPast()) && !$attendance) {
                                $displayStatus = '-';
                                $statusClass = 'text-gray-500 font-medium';
                            }
                        @endphp
                        <tr wire:key="{{ $employee->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $employee->name }}
                            </td>
                            <td class="px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $employee->jobTitle?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $attendance->shift?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="{{ $statusClass }}">{{ $displayStatus }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $timeIn ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $timeOut ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination dengan padding yang tepat --}}
        <div class="px-2">
            {{ $employees->links() }}
        </div>

        {{-- Modal component --}}
        @stack('attendance-detail-scripts')
    </div>
</div>
