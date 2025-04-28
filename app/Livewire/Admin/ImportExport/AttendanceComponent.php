<?php

namespace App\Livewire\Admin\ImportExport;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Exports\AttendancesExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\JobTitle;

class AttendanceComponent extends Component
{
    use AttendanceDetailTrait;
    use WithPagination, InteractsWithBanner;

    public bool $previewing = false;
    public string $mode = 'import';
    public bool $isLoading = false;

    // filter
    public ?string $month = null;
    public ?string $week = null;
    public ?string $year = null;
    public ?string $date = null;
    public ?string $division = null;
    public ?string $jobTitle = null; // pastikan penamaan konsisten camelCase
    public ?string $search = null;

    public $rawData;
    public $attendances;

    protected $polling = 30000;
    protected $listeners = ['refreshData' => '$refresh'];

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->rawData = collect([]);
        $this->attendances = collect([]);
    }

    public function updating($key): void
    {
        if (in_array($key, ['search', 'division', 'jobTitle'])) {
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

    public function clearAttendanceCache()
    {
        $today = date('Y-m-d');
        $users = User::where('group', 'user')->get();

        foreach ($users as $user) {
            Cache::forget("attendance-{$user->id}-{$today}");
        }

        $this->banner('Data absensi berhasil disegarkan');
    }

    public function preview()
    {
        $this->isLoading = true;
        $this->previewing = true;
        $this->mode = 'import';
        $this->isLoading = false;
    }

    public function export()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }

        $jobTitleName = $this->jobTitle ? JobTitle::find($this->jobTitle)?->name : null;

        $filename = 'attendances'
            . ($this->month ? '_' . Carbon::parse($this->month)->format('F-Y') : '')
            . ($this->year && !$this->month ? '_' . $this->year : '')
            . ($jobTitleName ? '_' . Str::slug($jobTitleName) : '')
            . '.xlsx';

        return Excel::download(new AttendancesExport(
            $this->month,
            $this->year,
            $this->jobTitle, // gunakan camelCase
        ), $filename);
    }

    public function render()
    {
        if ($this->date) {
            $dates = [Carbon::parse($this->date)];
        } elseif ($this->week) {
            $start = Carbon::parse($this->week)->startOfWeek();
            $end = Carbon::parse($this->week)->endOfWeek();
            $dates = $start->range($end)->toArray();
        } elseif ($this->month) {
            $start = Carbon::parse($this->month)->startOfMonth();
            $end = Carbon::parse($this->month)->endOfMonth();
            $dates = $start->range($end)->toArray();
        } else {
            $dates = [];
        }

        $employees = User::where('group', 'user')
            ->when($this->search, function (Builder $query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nip', 'like', '%' . $this->search . '%');
            })
            ->when($this->division, fn(Builder $query) => $query->where('division_id', $this->division))
            ->when($this->jobTitle, fn(Builder $query) => $query->where('job_title_id', $this->jobTitle))
            ->paginate(20)
            ->through(function (User $user) {
                $attendances = collect([]);

                if ($this->date) {
                    $attendances = new Collection(Cache::remember(
                        "attendance-{$user->id}-{$this->date}",
                        now()->addMinutes(2),
                        function () use ($user) {
                            return Attendance::filter(userId: $user->id, date: $this->date)->get()
                                ->map(fn($v) => $this->transformAttendance($v))
                                ->toArray();
                        }
                    ) ?? []);
                } elseif ($this->week) {
                    $attendances = new Collection(Cache::remember(
                        "attendance-{$user->id}-{$this->week}",
                        now()->addMinutes(5),
                        function () use ($user) {
                            return Attendance::filter(userId: $user->id, week: $this->week)->get()
                                ->map(fn($v) => $this->transformAttendance($v))
                                ->toArray();
                        }
                    ) ?? []);
                } elseif ($this->month) {
                    $my = Carbon::parse($this->month);
                    $attendances = new Collection(Cache::remember(
                        "attendance-{$user->id}-{$my->month}-{$my->year}",
                        now()->addMinutes(10),
                        function () use ($user) {
                            return Attendance::filter(userId: $user->id, month: $this->month)->get()
                                ->map(fn($v) => $this->transformAttendance($v))
                                ->toArray();
                        }
                    ) ?? []);
                } else {
                    $attendances = Attendance::where('user_id', $user->id)->get();
                }

                $user->attendances = $attendances;
                return $user;
            });

        return view('livewire.admin.import-export.attendance', [
            'employees' => $employees,
            'dates' => $dates,
            'rawData' => $this->rawData,
            'attendances' => $this->attendances,
        ]);
    }

    private function transformAttendance(Attendance $attendance)
    {
        $attendance->setAttribute('coordinates', $attendance->lat_lng);
        $attendance->setAttribute('lat', $attendance->latitude);
        $attendance->setAttribute('lng', $attendance->longitude);

        if ($attendance->attachment) {
            $attendance->setAttribute('attachment', $attendance->attachment_url);
        }
        if ($attendance->shift) {
            $attendance->setAttribute('shift', $attendance->shift->name);
        }

        return $attendance->getAttributes();
    }
}
