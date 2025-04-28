<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Name</th>
      <th>Time In</th>
      <th>Time Out</th>
      <th>Shift</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($attendances as $attendance)
      @php
        // Logika untuk menangani status
        $originalStatus = $attendance->status;
        
        // Jika status terlambat, ubah menjadi hadir untuk tampilan
        if ($originalStatus == 'late') {
          $displayStatus = 'present';
        } else {
          $displayStatus = $originalStatus;
        }
      @endphp
      <tr>
        <td>{{ $attendance->date?->format('Y-m-d') }}</td>
        <td>{{ $attendance->user?->name }}</td>
        <td>{{ $attendance->time_in?->format('H:i:s') }}</td>
        <td>{{ $attendance->time_out?->format('H:i:s') }}</td>
        <td>{{ $attendance->shift?->name }}</td>
        <td>{{ __($displayStatus) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>