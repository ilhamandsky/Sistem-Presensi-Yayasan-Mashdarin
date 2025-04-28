<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;

class AttendancesExport implements FromView
{
    public function __construct(
        private $month = null,
        private $year = null,
        private $jobTitle = null,
        
    ) {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $attendances = Attendance::filter(
            month: $this->month,
            year: $this->year,
            jobTitle: $this->jobTitle,
            
        )->get();

        return view('admin.import-export.export-attendances', ['attendances' => $attendances]);
    }
}
