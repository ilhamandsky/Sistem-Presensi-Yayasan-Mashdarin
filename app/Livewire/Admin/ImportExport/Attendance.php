<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceComponent extends Component
{
    use AttendanceDetailTrait;
    use WithPagination, InteractsWithBanner;

    // Tambahkan listener untuk tombol refresh manual
    protected $listeners = ['refreshData' => '$refresh'];

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function updating($key): void
    {

        }
    }

    // Tambahkan metode untuk membersihkan cache absensi
    public function clearAttendanceCache()
    {
        // Hapus semua cache yang berkaitan dengan absensi untuk hari ini
        $today = date('Y-m-d');
        $users = User::where('group', 'user')->get();

        foreach ($users as $user) {
            Cache::forget("attendance-$user->id-$today");
        }

        $this->banner('Data absensi berhasil disegarkan');
    }

    public function render()
    {
        if ($this->date) {
            $dates = [Carbon::parse($this->date)];
        } else if ($this->week) {
            $start = Carbon::parse($this->week)->startOfWeek();
            $end = Carbon::parse($this->week)->endOfWeek();
            $dates = $start->range($end)->toArray();
        } else if ($this->month) {
            $start = Carbon::parse($this->month)->startOfMonth();
            $end = Carbon::parse($this->month)->endOfMonth();
            $dates = $start->range($end)->toArray();
        }
    }
}
