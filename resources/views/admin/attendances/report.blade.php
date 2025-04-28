@php
    use Illuminate\Support\Carbon;
    $selectedDate = Carbon::parse($date ?? ($week ?? $month))->settings(['formatFunction' => 'translatedFormat']);
    $showUserDetail = !$month || $week || $date; // is week or day filter
    $isPerDayFilter = isset($date);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Absensi | {{ $date ?? ($week ?? $month) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        #table {
            border-collapse: collapse;
            width: 100%;
        }

        #table th,
        #table td {
            border: 1px solid #aaa;
            padding: 8px;
        }

        #table th {
            background-color: #f2f2f2;
        }

        #table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #table tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <h1>Data Presensi</h1>

    <div style="display: table; width: 100%; margin-bottom: 20px">
        <div style="display: table-cell;">
            <table>
                @if ($division)
                    <tr>
                        <td>Divisi</td>
                        <td>:</td>
                        <td>{{ App\Models\Division::find($division)->name ?? '-' }}</td>
                    </tr>
                @endif
                @if ($jobTitle)
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>{{ App\Models\JobTitle::find($jobTitle)->name ?? '-' }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div style="display: table-cell; text-align: right;">
            @if ($month)
                Bulan: {{ $selectedDate->format('F Y') }}
            @elseif ($week)
                Tanggal: {{ $start->format('l, d/m/Y') }} - {{ $end->format('l, d/m/Y') }}
            @elseif ($date)
                Tanggal: {{ $selectedDate->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <table id="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>{{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}</th>
                @if ($showUserDetail)
                    <th>{{ __('Job Title') }}</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    @if ($isPerDayFilter)
                        <th>{{ __('Shift') }}</th>
                    @endif
                @endif
                @foreach ($dates as $date)
                    <th style="padding: 0px 2px; font-size: 14px">
                        @if ($isPerDayFilter)
                            Status
                        @elseif (!$month)
                            {{ $date->format('d/m') }}
                        @else
                            {{ $date->format('d') }}
                        @endif
                    </th>
                @endforeach
                @if (!$isPerDayFilter)
                    <th>H</th>
                    <th>A</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                @php
                    $attendances = $employee->attendances;
                    $attendance = $attendances->first();
                @endphp
                <tr style="font-size: 12px">
                    <td style="text-align: center; vertical-align: middle; padding: 0px">{{ $loop->iteration }}</td>
                    <td>{{ $employee->name }}</td>

                    @if ($showUserDetail)
                        <td>{{ $employee->jobTitle?->name ?? '-' }}</td>
                        <td style="text-align: center;">
                            {{ $attendance['time_in'] ?? '-' }}
                        </td>
                        <td style="text-align: center;">
                            {{ $attendance['time_out'] ?? '-' }}
                        </td>
                        @if ($isPerDayFilter)
                            <td>{{ $attendance['shift'] ?? '-' }}</td>
                        @endif
                    @endif

                    @php
                        $presentCount = 0;
                        $absentCount = 0;
                    @endphp

                    @foreach ($dates as $date)
                        @php
                            $isWeekend = $date->isWeekend();
                            $attendanceData = $attendances->firstWhere(
                                fn($v, $k) => $v['date'] === $date->format('Y-m-d'),
                            );

                            if ($attendanceData) {
                                $originalStatus = $attendanceData['status'];
                                $status =
                                    $originalStatus == 'present' || $originalStatus == 'late' ? 'present' : 'absent';
                            } else {
                                $status = $isWeekend || !$date->isPast() ? '-' : 'absent';
                            }

                            switch ($status) {
                                case 'present':
                                    $shortStatus = 'H';
                                    $presentCount++;
                                    break;
                                case 'absent':
                                    $shortStatus = 'A';
                                    $absentCount++;
                                    break;
                                default:
                                    $shortStatus = '-';
                                    break;
                            }

                            $displayStatus = $isPerDayFilter
                                ? ($status == 'present'
                                    ? 'Hadir'
                                    : ($status == 'absent'
                                        ? 'Tidak Hadir'
                                        : '-'))
                                : $shortStatus;
                        @endphp

                        <td style="padding: 0px; text-align: center;">
                            {{ $displayStatus }}
                        </td>
                    @endforeach

                    @if (!$isPerDayFilter)
                        <td style="text-align: center;">{{ $presentCount }}</td>
                        <td style="text-align: center;">{{ $absentCount }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($employees->isEmpty())
        <div style="text-align: center; margin-top: 20px">
            Tidak ada data
        </div>
    @endif
</body>

</html>
