<div>
    <h1 class="text-2xl font-bold mb-4">Export Presensi</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Form -->
        <div>
            <label>Per Tahun</label>
            <input type="text" wire:model="year" class="form-input w-full" placeholder="YYYY">
        </div>
        <div>
            <label>Per Bulan</label>
            <input type="month" wire:model="month" class="form-input w-full">
        </div>
        <div>
            <label>Jabatan</label>
            <select wire:model="job_title" class="form-select w-full">
                <option value="">Semua Jabatan</option>
                @foreach (\App\Models\JobTitle::all() as $title)
                    <option value="{{ $title->id }}">{{ $title->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button wire:click="preview" class="bg-gray-500 text-white py-2 px-4 rounded">Pratinjau</button>
            <button wire:click="export" class="bg-indigo-600 text-white py-2 px-4 rounded">Ekspor</button>
        </div>
    </div>

    @if ($previewing)
        @if ($mode == 'import')
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border">
                    <thead class="bg-gray-100">
                        <tr>
                            @foreach ($rawData->first() ?? [] as $key => $value)
                                <th class="border px-4 py-2">{{ $key }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rawData as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="border px-4 py-2">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($mode == 'export')
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">No</th>
                            <th class="border px-4 py-2">Tanggal</th>
                            <th class="border px-4 py-2">Nama</th>
                            <th class="border px-4 py-2">Jam Masuk</th>
                            <th class="border px-4 py-2">Jam Keluar</th>
                            <th class="border px-4 py-2">Shift</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $index => $attendance)
                            @php
                                $shift = $attendance->shift ? json_decode($attendance->shift, true) : null;
                            @endphp
                            <tr>
                                <td class="border px-4 py-2">{{ $index + 1 }}</td>
                                <td class="border px-4 py-2">{{ $attendance->date }}</td>
                                <td class="border px-4 py-2">{{ $attendance->user->name ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $attendance->time_in?->format('H:i:s') }}</td>
                                <td class="border px-4 py-2">{{ $attendance->time_out?->format('H:i:s') }}</td>
                                <td class="border px-4 py-2">
                                    {{ $shift ? $shift['name'] . ' (' . $shift['start_time'] . '-' . $shift['end_time'] . ')' : '-' }}
                                </td>
                                <td class="border px-4 py-2">{{ $attendance->status ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $attendance->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
