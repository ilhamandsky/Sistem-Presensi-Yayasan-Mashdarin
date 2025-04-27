<?php

namespace App\Livewire\Admin\ImportExport;

use Livewire\Component;
use App\Models\Division;
use App\Models\JobTitle;
use App\Models\Education;
use App\Models\Attendance as AttendanceModel;
use App\Exports\AttendancesExport;
use App\Imports\AttendancesImport;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Laravel\Jetstream\InteractsWithBanner;

class Attendance extends Component
{
    use InteractsWithBanner, WithFileUploads;

    public bool $previewing = false;
    public ?string $mode = null;
    public $file = null;
    public $year = null;
    public $month = null;
    public $division = null;
    public $job_title = null;
    public $education = null;
    public $exportFormat = 'excel'; // default ke excel

    protected $rules = [
        'file' => 'required|mimes:csv,xls,xlsx,ods',
        'year' => 'nullable|date_format:Y',
        'month' => 'nullable|date_format:Y-m',
        'division' => 'nullable|exists:divisions,id',
        'job_title' => 'nullable|exists:job_titles,id',
        'education' => 'nullable|exists:educations,id',
    ];

    public function mount()
    {
        $this->year = date('Y');
    }

    public function preview()
    {
        $this->previewing = !$this->previewing;
        $this->mode = $this->previewing ? 'export' : null;
    }

    public function render()
    {
        $attendances = null;
        $rawData = null;

        if ($this->file) {
            $this->mode = 'import';
            $this->previewing = true;
            $attendanceImport = new AttendancesImport(save: false);
            $rawData = Excel::toCollection($attendanceImport, $this->file)->first();
        } elseif ($this->previewing && $this->mode == 'export') {
            $attendances = AttendanceModel::filter(
                month: $this->month,
                year: $this->year,
                division: $this->division,
                jobTitle: $this->job_title,
                education: $this->education
            )->get();
        } else {
            $this->previewing = false;
            $this->mode = null;
        }

        return view('livewire.admin.import-export.attendance', [
            'attendances' => $attendances,
            'rawData' => $rawData,
        ]);
    }


    public function import()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }
        try {
            $this->validate();

            Excel::import(new AttendancesImport, $this->file);

            $this->banner(__('Import berhasil!'));
            $this->reset();
        } catch (\Throwable $th) {
            $this->dangerBanner($th->getMessage());
        }
    }

    public function export()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }

        $division = $this->division ? Division::find($this->division)?->name : null;
        $job_title = $this->job_title ? JobTitle::find($this->job_title)?->name : null;
        $education = $this->education ? Education::find($this->education)?->name : null;

        $filename = 'attendances' .
            ($this->month ? '_' . Carbon::parse($this->month)->format('F-Y') : '') .
            ($this->year && !$this->month ? '_' . $this->year : '') .
            ($division ? '_' . Str::slug($division) : '') .
            ($job_title ? '_' . Str::slug($job_title) : '') .
            ($education ? '_' . Str::slug($education) : '');

        $attendances = AttendanceModel::filter(
            month: $this->month,
            year: $this->year,
            division: $this->division,
            jobTitle: $this->job_title,
            education: $this->education
        )->get();

        if ($this->exportFormat === 'pdf') {
            // Membuat HTML string untuk PDF
            $html = '<h1>Attendance Report</h1>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
            $html .= '<thead><tr><th>ID</th><th>Name</th><th>Division</th><th>Job Title</th><th>Attendance Date</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($attendances as $attendance) {
                $html .= '<tr>';
                $html .= '<td>' . $attendance->id . '</td>';
                $html .= '<td>' . $attendance->name . '</td>';
                $html .= '<td>' . ($attendance->division?->name ?? '-') . '</td>';
                $html .= '<td>' . ($attendance->jobTitle?->name ?? '-') . '</td>';
                $html .= '<td>' . $attendance->attendance_date . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';

            // Generate PDF from HTML string
            $pdf = Pdf::loadHTML($html);
            return response()->streamDownload(
                fn() => print($pdf->stream()),
                $filename . '.pdf'
            );
        }

        // Default Excel
        return Excel::download(new AttendancesExport(
            $this->month,
            $this->year,
            $this->division,
            $this->job_title,
            $this->education
        ), $filename . '.xlsx');
    }
}
