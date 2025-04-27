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

    # filter
    public ?string $month;
    public ?string $week = null;
    public ?string $date = null;
    public ?string $division = null;
    public ?string $jobTitle = null;
    public ?string $search = null;

    // Tambahkan polling interval (refresh setiap 30 detik)
    protected $polling = 30000;

    // Tambahkan listener untuk tombol refresh manual
    protected $listeners = ['refreshData' => '$refresh'];

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function updating($key): void
    {
        if ($key === 'search' || $key === 'division' || $key === 'jobTitle') {
            $this->resetPage();
        }
        if ($key === 'month') {
            $this->resetPage();
            $this->week = null;
            $this->date = null;
        }
        if ($key === 'week') {
            $this->resetPage();
            $this->month = null;
            $this->date = null;
        }
        if ($key === 'date') {
            $this->resetPage();
            $this->month = null;
            $this->week = null;
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
        $employees = User::where('group', 'user')
            ->when($this->search, function (Builder $q) {
                return $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nip', 'like', '%' . $this->search . '%');
            })
            ->when($this->division, fn(Builder $q) => $q->where('division_id', $this->division))
            ->when($this->jobTitle, fn(Builder $q) => $q->where('job_title_id', $this->jobTitle))
            ->paginate(20)->through(function (User $user) {
                if ($this->date) {
                    // Kurangi waktu cache menjadi 2 menit saja
                    $attendances = new Collection(Cache::remember(
                        "attendance-$user->id-$this->date",
                        now()->addMinutes(2), // Ubah dari addDay() menjadi addMinutes(2)
                        function () use ($user) {
                            /** @var Collection<Attendance>  */
                            $attendances = Attendance::filter(
                                userId: $user->id,
                                date: $this->date,
                            )->get();

                            return $attendances->map(
                                function (Attendance $v) {
                                    $v->setAttribute('coordinates', $v->lat_lng);
                                    $v->setAttribute('lat', $v->latitude);
                                    $v->setAttribute('lng', $v->longitude);
                                    if ($v->attachment) {
                                        $v->setAttribute('attachment', $v->attachment_url);
                                    }
                                    if ($v->shift) {
                                        $v->setAttribute('shift', $v->shift->name);
                                    }
                                    return $v->getAttributes();
                                }
                            )->toArray();
                        }
                    ) ?? []);
                } else if ($this->week) {
                    // Kurangi waktu cache
                    $attendances = new Collection(Cache::remember(
                        "attendance-$user->id-$this->week",
                        now()->addMinutes(5), // Ubah dari addDay() menjadi addMinutes(5)
                        function () use ($user) {
                            /** @var Collection<Attendance>  */
                            $attendances = Attendance::filter(
                                userId: $user->id,
                                week: $this->week,
                            )->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note']);

                            return $attendances->map(
                                function (Attendance $v) {
                                    $v->setAttribute('coordinates', $v->lat_lng);
                                    $v->setAttribute('lat', $v->latitude);
                                    $v->setAttribute('lng', $v->longitude);
                                    if ($v->attachment) {
                                        $v->setAttribute('attachment', $v->attachment_url);
                                    }
                                    return $v->getAttributes();
                                }
                            )->toArray();
                        }
                    ) ?? []);
                } else if ($this->month) {
                    $my = Carbon::parse($this->month);
                    // Kurangi waktu cache
                    $attendances = new Collection(Cache::remember(
                        "attendance-$user->id-$my->month-$my->year",
                        now()->addMinutes(10), // Ubah dari addDay() menjadi addMinutes(10)
                        function () use ($user) {
                            /** @var Collection<Attendance>  */
                            $attendances = Attendance::filter(
                                month: $this->month,
                                userId: $user->id,
                            )->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note']);

                            return $attendances->map(
                                function (Attendance $v) {
                                    $v->setAttribute('coordinates', $v->lat_lng);
                                    $v->setAttribute('lat', $v->latitude);
                                    $v->setAttribute('lng', $v->longitude);
                                    if ($v->attachment) {
                                        $v->setAttribute('attachment', $v->attachment_url);
                                    }
                                    return $v->getAttributes();
                                }
                            )->toArray();
                        }
                    ) ?? []);
                } else {
                    /** @var Collection */
                    $attendances = Attendance::where('user_id', $user->id)
                        ->get(['id', 'status', 'date', 'latitude', 'longitude', 'attachment', 'note']);
                }
                $user->attendances = $attendances;
                return $user;
            });
        return view('livewire.admin.attendance', ['employees' => $employees, 'dates' => $dates]);
    }
}
