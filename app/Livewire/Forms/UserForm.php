<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Form
{
    public ?User $user = null;
    public $job_title_id = null;
    public $name = '';
    public $email = '';
    public $password = null;
    public $gender = null;
    public $group = 'admin';
    public $photo = null;
    public $is_employee = false; // Tambahkan flag untuk menandai form karyawan

    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($this->user?->id),
            ],
            'gender' => ['nullable', 'in:male,female'],
            'group' => ['nullable', 'string', 'max:255', Rule::in(User::$groups)],
            'job_title_id' => ['nullable', 'exists:job_titles,id'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        // Hanya tambahkan validasi email dan password jika bukan form karyawan
        if (!$this->is_employee) {
            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ];
        }

        return $rules;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->gender = $user->gender;
        $this->group = $user->group;
        $this->job_title_id = $user->job_title_id;
        return $this;
    }

    public function setAsEmployee()
    {
        $this->is_employee = true;
        $this->group = 'user'; // Set default group untuk karyawan
        return $this;
    }

    public function store()
    {
        // Cek otorisasi terlebih dahulu
        if (!$this->isAllowed()) {
            return abort(403);
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'gender' => $this->gender,
            'group' => $this->group,
            'job_title_id' => $this->job_title_id,
        ];

        // Jika bukan form karyawan, tambahkan email dan password
        if (!$this->is_employee) {
            $data['email'] = $this->email;
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
                $data['raw_password'] = $this->password;
            }
        } else {
            // Untuk karyawan, generate email dan password sederhana
            $data['email'] = null;
            $defaultPassword = 'password'; // Ganti dengan password default yang lebih aman
            $data['password'] = Hash::make($defaultPassword);
            $data['raw_password'] = $defaultPassword;
        }

        $user = User::create($data);

        if ($this->photo) {
            $user->updateProfilePhoto($this->photo);
        }

        $this->reset('name', 'email', 'password', 'gender', 'photo', 'job_title_id');
        $this->user = null;
    }

    public function update()
    {
        if (!$this->isAllowed()) {
            return abort(403);
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'gender' => $this->gender,
            'group' => $this->group,
            'job_title_id' => $this->job_title_id,
        ];

        // Jika bukan form karyawan, update email dan password jika ada
        if (!$this->is_employee) {
            $data['email'] = $this->email;
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
                $data['raw_password'] = $this->password;
            }
        }

        $this->user->update($data);

        if ($this->photo) {
            $this->user->updateProfilePhoto($this->photo);
        }

        $this->reset('name', 'email', 'password', 'gender', 'photo', 'job_title_id');
        $this->user = null;
    }

    public function deleteProfilePhoto()
    {
        if (!$this->user || !$this->isAllowed()) {
            return abort(403);
        }

        return $this->user->deleteProfilePhoto();
    }

    public function delete()
    {
        if (!$this->user || !$this->isAllowed()) {
            return abort(403);
        }

        $this->user->delete();
        $this->reset();
    }

    private function isAllowed()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser) {
            return false;
        }

        // Jika membuat user baru
        if (!$this->user) {
            // Admin dapat membuat karyawan (user)
            if ($this->is_employee && $currentUser->isAdmin) {
                return true;
            }
            
            // Hanya superadmin yang dapat membuat admin
            if (!$this->is_employee && $this->group === 'admin') {
                return $currentUser->isSuperadmin;
            }
        } 
        // Jika mengedit user yang sudah ada
        else {
            // Admin dapat mengedit karyawan
            if ($this->user->group === 'user' && $currentUser->isAdmin) {
                return true;
            }
            
            // Admin dapat mengedit dirinya sendiri
            if ($this->user->group === 'admin' && $currentUser->isAdmin && $currentUser->id === $this->user->id) {
                return true;
            }
            
            // Superadmin dapat mengedit siapa saja
            if ($currentUser->isSuperadmin) {
                return true;
            }
        }

        return false;
    }
}
