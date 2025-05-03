<div class="w-full">
  @php
    use Illuminate\Support\Carbon;
    $startTime = $selectedShift?->start_time ? Carbon::parse($selectedShift->start_time)->format('H:i') : '--:--';
    $endTime = $selectedShift?->end_time ? Carbon::parse($selectedShift->end_time)->format('H:i') : '--:--';
    
    // Hitung waktu minimum untuk absen keluar jika sudah absen masuk
    $minTimeOut = null;
    if ($attendance && $attendance->time_in) {
        $minTimeOut = Carbon::parse($attendance->time_in)->addMinutes(10)->format('H:i');
    }
  @endphp

  @if (!$isAbsence)
    <script src="{{ url('/assets/js/html5-qrcode.min.js') }}"></script>
  @endif

  <div class="flex flex-col items-center gap-6 px-4 py-8">
    @if (!$isAbsence)
      <div class="w-full max-w-md">
        <x-select id="shift" class="mt-1 block w-full" wire:model.live="shift_id" :disabled="$attendance !== null">
          <option value="">{{ __('Select Shift') }}</option>
          @foreach ($shifts as $shift)
            <option value="{{ $shift->id }}" {{ $shift->id == $shift_id ? 'selected' : '' }}>
              {{ $shift->name . ' | ' . Carbon::parse($shift->start_time)->format('H:i') . ' - ' . Carbon::parse($shift->end_time)->format('H:i') }}
            </option>
          @endforeach
        </x-select>
        @error('shift_id') <x-input-error for="shift_id" class="mt-2" :message="$message" /> @enderror
      </div>

      <!-- Status Presensi -->
      <div class="w-full max-w-2xl mb-4">
        <div class="text-center p-2 rounded-md {{ $canCheckIn || $canCheckOut ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}"
             data-can-check-in="{{ $canCheckIn ? 'true' : 'false' }}"
             data-can-check-out="{{ $canCheckOut ? 'true' : 'false' }}">
          <p>{{ $statusMessage }}</p>
        </div>
      </div>

      <div class="w-full max-w-2xl rounded-lg border border-gray-300 p-2 dark:border-gray-600" wire:ignore>
        <div id="scanner" class="h-96 w-full rounded-md outline-dashed outline-slate-500 md:h-[28rem] lg:h-[32rem]">
          <p class="flex h-full items-center justify-center text-center text-gray-500">
             Mengaktifkan kamera...
          </p>
        </div>
      </div>
    @endif

    <div class="w-full max-w-2xl text-center">
      <h4 id="scanner-error" class="mb-2 min-h-[1.5em] text-lg font-semibold text-red-500 dark:text-red-400 sm:text-xl" wire:ignore></h4>
      <h4 id="scanner-result" class="mb-2 {{ $successMsg ? '' : 'hidden' }} min-h-[1.5em] text-lg font-semibold text-green-500 dark:text-green-400 sm:text-xl">
        {{ $successMsg }}
      </h4>
    </div>

    <h4 id="status-info" class="text-lg font-semibold text-gray-600 dark:text-gray-100 sm:text-xl">
      {{ __('Date') . ': ' . now()->format('d/m/Y') }}
    </h4>

    <div class="grid w-full max-w-2xl grid-cols-2 gap-4">
      <div class="flex items-center justify-between rounded-md bg-blue-200 px-4 py-3 text-gray-800 dark:bg-blue-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-base font-semibold md:text-lg">Jam Masuk Shift</h4>
          <span class="text-lg font-medium">{{ $startTime }}</span>
          @if($attendance?->time_in)
            <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">
              Absen: {{ Carbon::parse($attendance->time_in)->format('H:i:s') }}
            </p>
          @endif
        </div>
        <x-heroicon-o-clock class="h-6 w-6 flex-shrink-0" />
      </div>
      <div class="flex items-center justify-between rounded-md bg-orange-200 px-4 py-3 text-gray-800 dark:bg-orange-900 dark:text-white dark:shadow-gray-700">
        <div>
          <h4 class="text-base font-semibold md:text-lg">Jam Keluar Shift</h4>
          <span class="text-lg font-medium">{{ $endTime }}</span>
          @if($attendance?->time_out)
            <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">
              Absen: {{ Carbon::parse($attendance->time_out)->format('H:i:s') }}
            </p>
          @elseif($attendance?->time_in && !$attendance?->time_out)
            <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">
              @if($minTimeOut)
                <br>Dapat absen keluar mulai: {{ $minTimeOut }}
              @endif
            </p>
          @endif
        </div>
        <x-heroicon-o-clock class="h-6 w-6 flex-shrink-0" />
      </div>
    </div>
  </div>
</div>

@if (!$isAbsence)
@script
<script>
(function() {
  const errorMsg = document.querySelector('#scanner-error');
  const shift = document.querySelector('#shift');
  let isRendered = false;
  let scanner = null;
  // Simpan ID karyawan yang sedang dalam masa jeda
  let cooldownEmployees = {};

  setTimeout(() => {
    if (!shift.value) {
      errorMsg.innerHTML = 'Pilih shift terlebih dahulu';
    } else {
      startScanning();
      isRendered = true;
    }
  }, 1000);

  shift.addEventListener('change', () => {
    if (!isRendered && shift.value) {
      startScanning();
      isRendered = true;
      errorMsg.innerHTML = '';
    }
    if (!shift.value && scanner) {
      scanner.pause(true);
      errorMsg.innerHTML = 'Pilih shift terlebih dahulu';
    } else if (scanner && scanner.getState() === Html5QrcodeScannerState.PAUSED) {
      scanner.resume();
      errorMsg.innerHTML = '';
    }
  });

  async function startScanning() {
    if (!scanner) scanner = new Html5Qrcode('scanner');
    if (scanner.getState() === Html5QrcodeScannerState.PAUSED) {
      return scanner.resume();
    }
    await scanner.start(
      { facingMode: "environment" },
      { fps: 15, qrbox: { width: 280, height: 280 } },
      onScanSuccess
    );
  }

  async function onScanSuccess(decodedText) {
    if (scanner.getState() === Html5QrcodeScannerState.SCANNING) {
      scanner.pause(true);
    }
    
    // Cek apakah karyawan ini dalam masa jeda
    if (cooldownEmployees[decodedText]) {
      const cooldownEndTime = cooldownEmployees[decodedText];
      const now = new Date();
      
      if (now < cooldownEndTime) {
        // Masih dalam masa jeda
        const remainingMinutes = Math.ceil((cooldownEndTime - now) / 60000);
        errorMsg.innerHTML = `Karyawan ini baru saja absen masuk. Dapat absen keluar setelah ${remainingMinutes} menit lagi.`;
        
        // Resume scanner setelah menampilkan pesan, tanpa menghapus cooldown
        setTimeout(() => {
          scanner.resume();
        }, 3000);
        return;
      } else {
        // Jeda sudah berakhir, hapus dari daftar cooldown
        delete cooldownEmployees[decodedText];
      }
    }
    
    const result = await $wire.scan(decodedText);
    
    if (result === true) {
      // Scan berhasil
      scanner.resume(); // Langsung resume scanner untuk karyawan berikutnya
      errorMsg.innerHTML = '';
      document.querySelector('#scanner-result').classList.remove('hidden');
      
      // Jika ini adalah absen masuk yang berhasil, tambahkan ke daftar cooldown
      if ($wire.successMsg.includes('absen masuk')) {
        const cooldownEndTime = new Date();
        cooldownEndTime.setMinutes(cooldownEndTime.getMinutes() + 10);
        cooldownEmployees[decodedText] = cooldownEndTime;
      }
      
      // Bersihkan pesan sukses setelah beberapa detik
      setTimeout(() => {
        document.querySelector('#scanner-result').classList.add('hidden');
      }, 5000);
    } else if (typeof result === 'string') {
      // Pesan error
      errorMsg.innerHTML = result;
      
      // Cek apakah ini adalah pesan tentang jeda 10 menit
      if (result.includes('belum dapat absen keluar') && result.includes('10 menit setelah absen masuk')) {
        // Ekstrak waktu dari pesan
        const timeMatch = result.match(/setelah (\d{2}:\d{2})/);
        if (timeMatch && timeMatch[1]) {
          const [hours, minutes] = timeMatch[1].split(':').map(Number);
          const cooldownEndTime = new Date();
          cooldownEndTime.setHours(hours, minutes, 0, 0);
          
          // Simpan ke daftar cooldown
          cooldownEmployees[decodedText] = cooldownEndTime;
        }
      }
      
      // Resume scanner setelah menampilkan pesan
      setTimeout(() => {
        scanner.resume();
        errorMsg.innerHTML = '';
      }, 3000);
    }
  }
})();
</script>
@endscript
@endif
