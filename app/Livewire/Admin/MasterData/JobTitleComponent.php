<?php

namespace App\Livewire\Admin\MasterData;

use App\Models\JobTitle;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class JobTitleComponent extends Component
{
    use InteractsWithBanner;

    public $name = '';
    public $deleteName = null;
    public $creating = false;
    public $editing = false;
    public $confirmingDeletion = false;
    public $selectedId = null;

    protected $rules = [
        'name' => ['required', 'string', 'max:255', 'unique:job_titles'],
    ];

    protected function messages()
    {
        return [
            'name.required' => 'Nama jabatan wajib diisi',
            'name.unique' => 'Nama jabatan sudah digunakan',
        ];
    }
    public function showCreating()
    {
        $this->resetErrorBag();
        $this->reset();
        $this->creating = true;
    }

    public function create()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        JobTitle::create(['name' => $this->name]);
        $this->creating = false;
        $this->name = null;
        $this->banner(__('Jabatan berhasil ditambahkan.'));
    }

    public function edit($id)
    {
        $this->resetErrorBag();
        $this->editing = true;
        $jobTitle = JobTitle::find($id);
        $this->name = $jobTitle->name;
        $this->selectedId = $id;
    }

    public function update()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        $jobTitle = JobTitle::find($this->selectedId);
        $jobTitle->update(['name' => $this->name]);
        $this->editing = false;
        $this->selectedId = null;
        $this->banner(__('Data jabatan berhasil diperbarui.'));
    }

    public function confirmDeletion($id, $name)
    {
        $this->deleteName = $name;
        $this->confirmingDeletion = true;
        $this->selectedId = $id;
    }

    public function delete()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $jobTitle = JobTitle::find($this->selectedId);
        $jobTitle->delete();
        $this->confirmingDeletion = false;
        $this->selectedId = null;
        $this->deleteName = null;
        $this->banner(__('Data jabatan berhasil dihapus.'));
    }

    public function render()
    {
        $jobTitles = JobTitle::all();
        return view('livewire.admin.master-data.job-title', ['jobTitles' => $jobTitles]);
    }
}
