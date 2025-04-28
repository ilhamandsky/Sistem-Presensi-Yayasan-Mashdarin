@php
  $date = Carbon\Carbon::now();
@endphp
<div>
  @pushOnce('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  @endpushOnce
  <div class="flex flex-col justify-between sm:flex-row">
    <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
      Presensi Hari Ini
    </h3>
    <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
      Jumlah Karyawan: {{ $employeesCount }}
    </h3>
  </div>
  <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
    <div class="rounded-md bg-green-200 px-8 py-4 text-gray-800 dark:bg-green-900 dark:text-white dark:shadow-gray-700">
      <span class="text-2xl font-semibold md:text-3xl">Hadir: {{ $presentCount }}</span><br>
    </div>

    <div class="rounded-md bg-red-200 px-8 py-4 text-gray-800 dark:bg-red-900 dark:text-white dark:shadow-gray-700">
      <span class="text-2xl font-semibold md:text-3xl">Tidak Hadir: {{ $absentCount }}</span><br>
      <span>Tidak/Belum Hadir</span>
    </div>
  </div>

  <div class="mb-4 overflow-x-scroll">
    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Name') }}
          </th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Job Title') }}
          </th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Shift') }}
          </th>
          <th scope="col"
            class="text-nowrap px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
            Status
          </th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Time In') }}
          </th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Time Out') }}
          </th>
          <th scope="col" class="relative">
            <span class="sr-only">Actions</span>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
        @php
          $class = 'px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
        @endphp
        @foreach ($employees as $employee)
          @php
            $attendance = $employee->attendance;
            $timeIn = $attendance ? $attendance?->time_in?->format('H:i') : null;
            $timeOut = $attendance ? $attendance?->time_out?->format('H:i') : null;
            $isWeekend = $date->isWeekend();
            $isPresent = $attendance && ($attendance['status'] == 'present' || $attendance['status'] == 'late');

            if ($isPresent) {
                $displayStatus = 'Hadir';
                $bgColor = 'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700';
            } else {
                $displayStatus = 'Tidak Hadir';
                $bgColor = 'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700';
            }

            // Jika akhir pekan atau tanggal di masa depan dan tidak ada absensi
            if (($isWeekend || !$date->isPast()) && !$attendance) {
                $displayStatus = '-';
                $bgColor = 'hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600';
            }
          @endphp
          <tr wire:key="{{ $employee->id }}" class="group">
            {{-- Detail karyawan --}}
            <td class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
              {{ $employee->name }}
            </td>
            <td class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
              {{ $employee->jobTitle?->name ?? '-' }}
            </td>
            <td class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
              {{ $attendance->shift?->name ?? '-' }}
            </td>

            {{-- Absensi --}}
            <td
              class="{{ $bgColor }} text-nowrap px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
              {{ $displayStatus }}
            </td>

            {{-- Waktu masuk/keluar --}}
            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
              {{ $timeIn ?? '-' }}
            </td>
            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
              {{ $timeOut ?? '-' }}
            </td>

            {{-- Action --}}
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{ $employees->links() }}

  <x-attendance-detail-modal :current-attendance="$currentAttendance" />
  @stack('attendance-detail-scripts')
</div>
