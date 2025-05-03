<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class EmployeeComponent extends Component
{
    use WithPagination, InteractsWithBanner, WithFileUploads;

    public UserForm $form;
    public $deleteName = null;
    public $creating = false;
    public $editing = false;
    public $confirmingDeletion = false;
    public $selectedId = null;
    public $showDetail = null;

    # filter
    public ?string $jobTitle = null;
    public ?string $search = null;

    public function mount()
    {
        // Set form sebagai form karyawan
        $this->form->setAsEmployee();
    }

    public function show($id)
    {
        $this->form->setUser(User::find($id));
        $this->showDetail = true;
    }

    public function showCreating()
    {
        $this->form->resetErrorBag();
        $this->form->reset();
        // Pastikan flag is_employee tetap true setelah reset
        $this->form->setAsEmployee();
        $this->creating = true;
        // Password default tidak perlu diatur karena akan digenerate otomatis
    }

    public function create()
    {
        // Pastikan form diatur sebagai employee sebelum menyimpan
        $this->form->setAsEmployee();
        $this->form->store();
        $this->creating = false;
        $this->banner(__('Berhasil dibuat.'));
    }

    public function edit($id)
    {
        $this->form->resetErrorBag();
        $this->form->reset();
        // Pastikan flag is_employee tetap true setelah reset
        $this->form->setAsEmployee();
        $this->editing = true;
        /** @var User $user */
        $user = User::find($id);
        $this->form->setUser($user);
    }

    public function update()
    {
        // Pastikan form diatur sebagai employee sebelum memperbarui
        $this->form->setAsEmployee();
        $this->form->update();
        $this->editing = false;
        $this->banner(__('Berhasil diupdate.'));
    }

    public function deleteProfilePhoto()
    {
        $this->form->deleteProfilePhoto();
    }

    public function confirmDeletion($id, $name)
    {
        $this->deleteName = $name;
        $this->confirmingDeletion = true;
        $this->selectedId = $id;
    }

    public function delete()
    {
        $user = User::find($this->selectedId);
        // Pastikan form diatur sebagai employee sebelum menghapus
        $this->form->setUser($user)->setAsEmployee()->delete();
        $this->confirmingDeletion = false;
        $this->banner(__('Berhasil dihapus.'));
    }

    public function render()
    {
        $users = User::where('group', 'user')
            ->when($this->search, function (Builder $q) {
                return $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->jobTitle, fn (Builder $q) => $q->where('job_title_id', $this->jobTitle))
            ->orderBy('name')
            ->paginate(20);
        return view('livewire.admin.employees', ['users' => $users]);
    }
}
