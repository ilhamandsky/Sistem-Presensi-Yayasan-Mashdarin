<?php

namespace App\Livewire\Forms;

use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ShiftForm extends Form
{
    // ⚠️ Tambahkan inisialisasi = null, jadi tidak error kalau belum di‑set
    public ?Shift $shift = null;

    public $name       = '';
    public $start_time = null;
    public $end_time   = null;

    /**
     * Dipanggil sebelum komponen mount,
     * kalau kamu mau langsung mengisi form untuk edit.
     * (pastikan Livewire memang memanggil mount pada Form)
     */
    public function mount(Shift $shift = null)
    {
        if ($shift) {
            $this->setShift($shift);
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama shift wajib diisi',
        ];
    }

    /**
     * Aturan validasi.
     */
    public function rules()
    {
        return [
            'name'       => [
                'required',
                'string',
                'max:255',
                // ignore berdasarkan id (bukan model langsung)
                Rule::unique('shifts', 'name')
                    ->ignore($this->shift?->id),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Isi form dari model Shift (dipakai untuk edit).
     */
    public function setShift(Shift $shift)
    {
        $this->shift       = $shift;
        $this->name        = $shift->name;
        $this->start_time  = $shift->start_time;
        $this->end_time    = $shift->end_time;
        return $this;
    }

    /**
     * Simpan data baru.
     */
    public function store()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }

        $this->validate();

        Shift::create([
            'name'       => $this->name,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
        ]);

        session()->flash('success', 'Shift berhasil dibuat.');
        $this->reset();  // reset form
    }

    /**
     * Update data existing.
     */
    public function update()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }

        $this->validate();

        $this->shift->update([
            'name'       => $this->name,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
        ]);

        session()->flash('success', 'Shift berhasil diperbarui.');
        $this->reset();
    }

    /**
     * Hapus shift.
     */
    public function delete()
    {
        if (Auth::user()->isNotAdmin) {
            abort(403);
        }

        $this->shift->delete();

        session()->flash('success', 'Shift berhasil dihapus.');
        $this->reset();
    }
}
