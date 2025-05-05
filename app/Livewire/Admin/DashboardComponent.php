<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\JobTitle;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;

    public function render()
    {
        /** @var Collection<Attendance>  */
        $attendances = Attendance::where('date', date('Y-m-d'))->get();

        /** @var Collection<User>  */
        $employees = User::where('group', 'user')
            ->paginate(20)
            ->through(function (User $user) use ($attendances) {
                return $user->setAttribute(
                    'attendance',
                    $attendances
                        ->where(fn (Attendance $attendance) => $attendance->user_id === $user->id)
                        ->first(),
                );
            });

        $employeesCount = User::where('group', 'user')->count();

        $jobTitleCount = JobTitle::count();

        $shiftCount = Shift::count();

        // Menggabungkan semua status yang dianggap "Hadir" (present dan late)
        $presentCount = $attendances->whereIn('status', ['present', 'late'])->count();

        // Menghitung "Tidak Hadir" sebagai total karyawan dikurangi yang hadir
        $absentCount = $employeesCount - $presentCount;

        return view('livewire.admin.dashboard', [
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'jobTitleCount' => $jobTitleCount,
            'shiftCount' => $shiftCount,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
        ]);
    }
}
