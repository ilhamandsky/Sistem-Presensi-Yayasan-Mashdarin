<?php

namespace App\Livewire;

use App\Models\Shift;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $shift_id = null;
    public $shifts = [];
    public ?Shift $selectedShift = null;
    public string $successMsg = '';
    public bool $isAbsence = false;
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $statusMessage = '';

    public function mount()
    {
        // Ambil semua shift, simpan sebagai koleksi (Collection)
        $this->shifts = Shift::orderBy('start_time')->get();

        // Set default shift jika ada
        $this->setDefaultShift();

        // Jika sudah ada shift_id, muat detail shift
        if ($this->shift_id) {
            $this->loadSelectedShift();
            $this->checkTimeValidity();
        }
    }

    public function loadInitialAttendance($userId = null)
    {
        $today = Carbon::today();
        $userId = $userId ?: Auth::id();
        if (!$userId) return;

        $this->attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if ($this->attendance) {
            $this->shift_id = $this->attendance->shift_id;
            $this->isAbsence = $this->attendance->time_out !== null;
            if ($this->isAbsence) {
                $this->successMsg = 'Sudah menyelesaikan absensi hari ini.';
            }
        } else {
            $this->isAbsence = false;
            $this->successMsg = '';
        }
        
        $this->checkTimeValidity();
    }

    public function setDefaultShift()
    {
        // Cek jika shifts kosong, jangan lakukan apa-apa
        if (empty($this->shifts) || count($this->shifts) === 0) {
            $this->shift_id = null;
            return;
        }

        $now = Carbon::now();
        $closestShift = null;
        $minDiff = null;

        foreach ($this->shifts as $shift) {
            $shiftStartTimeToday = Carbon::today()->setTimeFromTimeString($shift->start_time);
            $diff = abs($shiftStartTimeToday->diffInMinutes($now));
            if ($minDiff === null || $diff < $minDiff) {
                $minDiff = $diff;
                $closestShift = $shift;
            }
        }

        // Set shift_id ke shift terdekat, fallback ke shift pertama jika tidak ada
        $this->shift_id = $closestShift?->id ?? $this->shifts->first()?->id ?? null;
    }

    public function updatedShiftId($value)
    {
        $this->loadSelectedShift();
        $this->successMsg = '';
        $this->checkTimeValidity();
        $this->dispatch('clear-scanner-error');
    }

    private function loadSelectedShift()
    {
        $this->selectedShift = $this->shift_id ? Shift::find($this->shift_id) : null;
    }

    /**
     * Memeriksa apakah waktu saat ini valid untuk absen masuk atau keluar
     */
    public function checkTimeValidity()
    {
        if (!$this->selectedShift) {
            $this->canCheckIn = false;
            $this->canCheckOut = false;
            $this->statusMessage = 'Pilih shift terlebih dahulu';
            return;
        }

        $now = Carbon::now();
        
        // Dapatkan waktu shift
        $startTime = Carbon::parse($this->selectedShift->start_time)->setDate(
            $now->year, $now->month, $now->day
        );
        $endTime = Carbon::parse($this->selectedShift->end_time)->setDate(
            $now->year, $now->month, $now->day
        );
        
        // Cek apakah ada attendance hari ini
        $today = Carbon::today();
        $userId = Auth::id();
        $this->attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // Tentukan status absensi
        if (!$this->attendance) {
            // Belum absen sama sekali - cek apakah bisa absen masuk
            
            // Karyawan hanya bisa absen masuk mulai dari jam masuk yang ditentukan (tidak bisa lebih awal)
            // dan tidak bisa absen masuk setelah jam pulang (terlalu telat)
            $this->canCheckIn = $now->gte($startTime) && $now->lt($endTime);
            $this->canCheckOut = false;
            
            if ($this->canCheckIn) {
                $this->statusMessage = 'Anda dapat melakukan absen masuk sekarang';
            } else if ($now->lt($startTime)) {
                $this->statusMessage = 'Belum waktunya absen masuk. Absen masuk dapat dilakukan mulai ' . $startTime->format('H:i');
            } else {
                $this->statusMessage = 'Waktu absen masuk telah berakhir';
            }
        } else if (!$this->attendance->time_out) {
            // Sudah absen masuk, belum absen keluar
            
            // Cek jeda 10 menit setelah absen masuk
            $timeIn = Carbon::parse($this->attendance->time_in);
            $minTimeOut = $timeIn->copy()->addMinutes(10);
            
            // Karyawan tidak bisa absen keluar sebelum 10 menit dari waktu absen masuk
            // dan tidak bisa absen keluar setelah melewati jam absen keluar yang ditentukan
            $this->canCheckIn = false;
            $this->canCheckOut = $now->gte($minTimeOut) && $now->lte($endTime);
            
            if ($now->lt($minTimeOut)) {
                $this->statusMessage = 'Anda dapat melakukan absen keluar setelah ' . $minTimeOut->format('H:i') . ' (10 menit setelah absen masuk)';
            } else if ($this->canCheckOut) {
                $this->statusMessage = 'Anda dapat melakukan absen keluar sekarang';
            } else {
                $this->statusMessage = 'Waktu absen keluar telah berakhir';
            }
        } else {
            // Sudah absen masuk dan keluar
            $this->canCheckIn = false;
            $this->canCheckOut = false;
            $this->statusMessage = 'Anda sudah melakukan absen masuk dan keluar hari ini';
        }
    }

    /**
     * Method yang dipanggil oleh JS saat QR code discan.
     * $scannedUserId = isi QR code (ID karyawan)
     */
    public function scan(string $scannedUserId)
    {
        // Hanya admin yang bisa scan
        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin) {
            return 'Error: Hanya admin yang bisa melakukan scan.';
        }
        
        // Validasi shift
        if (!$this->shift_id) {
            return 'Pilih shift terlebih dahulu.';
        }
        
        if (!$this->selectedShift) {
            $this->loadSelectedShift();
            if (!$this->selectedShift) return 'Shift yang dipilih tidak valid.';
        }

        // Cari user karyawan berdasarkan isi QR code
        $employee = User::find($scannedUserId);
        if (!$employee) {
            return 'Error: QR Code tidak dikenali (user tidak ditemukan).';
        }

        // Cek absensi hari ini untuk karyawan
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->time_out) {
            return 'Karyawan sudah menyelesaikan absensi hari ini.';
        }

        $now = Carbon::now();
        
        // Dapatkan waktu shift
        $startTime = Carbon::parse($this->selectedShift->start_time)->setDate(
            $now->year, $now->month, $now->day
        );
        $endTime = Carbon::parse($this->selectedShift->end_time)->setDate(
            $now->year, $now->month, $now->day
        );

        try {
            if (!$attendance) {
                // Validasi waktu absen masuk
                // Karyawan hanya bisa absen masuk mulai dari jam masuk yang ditentukan (tidak bisa lebih awal)
                if ($now->lt($startTime)) {
                    return 'Belum waktunya absen masuk. Absen masuk dapat dilakukan mulai ' . $startTime->format('H:i');
                }
                
                // Karyawan tidak bisa absen masuk setelah jam pulang (terlalu telat)
                if ($now->gte($endTime)) {
                    return 'Waktu absen masuk telah berakhir';
                }
                
                // Absen Masuk
                $lateToleranceMinutes = $this->selectedShift->late_tolerance ?? 0;
                $deadlineTime = $startTime->copy()->addMinutes($lateToleranceMinutes);
                $status = $now->gt($deadlineTime) ? 'late' : 'present';
                $employeeBarcodeId = $employee->barcode?->id;

                Attendance::create([
                    'user_id'     => $employee->id,
                    'shift_id'    => $this->shift_id,
                    'barcode_id'  => $employeeBarcodeId,
                    'date'        => $now->toDateString(),
                    'time_in'     => $now,
                    'time_out'    => null,
                    'status'      => $status,
                ]);
                
                $this->successMsg = 'Berhasil absen masuk untuk ' . $employee->name;
                return true;
            } elseif (!$attendance->time_out) {
                // Validasi waktu absen keluar
                // Cek jeda 10 menit setelah absen masuk
                $timeIn = Carbon::parse($attendance->time_in);
                $minTimeOut = $timeIn->copy()->addMinutes(10);
                
                // Karyawan tidak bisa absen keluar sebelum 10 menit dari waktu absen masuk
                if ($now->lt($minTimeOut)) {
                    // Kembalikan pesan error dengan informasi tentang karyawan tertentu
                    return $employee->name . ' belum dapat absen keluar. Absen keluar dapat dilakukan setelah ' . $minTimeOut->format('H:i') . ' (10 menit setelah absen masuk)';
                }
                
                // Karyawan tidak bisa absen keluar setelah melewati jam absen keluar yang ditentukan
                if ($now->gt($endTime)) {
                    return 'Waktu absen keluar telah berakhir';
                }
                
                // Absen Keluar
                $attendance->update(['time_out' => $now]);
                $this->successMsg = 'Berhasil absen keluar untuk ' . $employee->name;
                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Attendance Scan Error: ' . $e->getMessage());
            return 'Terjadi kesalahan saat menyimpan absensi. Silakan coba lagi.';
        }
    }

    public function render()
    {
        if ($this->shift_id && !$this->selectedShift) {
            $this->loadSelectedShift();
            $this->checkTimeValidity();
        }
        
        return view('livewire.scan', [
            'shifts' => $this->shifts,
            'attendance' => $this->attendance,
            'isAbsence' => $this->isAbsence,
            'successMsg' => $this->successMsg,
            'shift_id' => $this->shift_id,
            'selectedShift' => $this->selectedShift,
            'canCheckIn' => $this->canCheckIn,
            'canCheckOut' => $this->canCheckOut,
            'statusMessage' => $this->statusMessage
        ]);
    }

    protected $listeners = ['clear-scanner-error' => '$refresh'];
}
