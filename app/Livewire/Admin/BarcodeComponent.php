<?php

namespace App\Livewire\Admin;

use App\Models\Barcode;
use Livewire\Component;
use Livewire\WithPagination;
use Laravel\Jetstream\InteractsWithBanner;

class BarcodeComponent extends Component
{
    use WithPagination, InteractsWithBanner;

    // Properties untuk konfirmasi penghapusan
    public $confirmingDeletion = false;
    public $selectedId = null;
    public $deleteName = null;

    // Properties untuk filter (jika diperlukan)
    public $search = '';

    public function render()
    {
        // Ambil barcodes dengan relasi user, urutkan berdasarkan nama user
        $barcodes = Barcode::with('user')
                           ->whereHas('user') // Pastikan hanya barcode yg punya user valid
                           ->join('users', 'barcodes.user_id', '=', 'users.id') // Join untuk sorting by user name
                           ->when($this->search, function($query) {
                               return $query->where('users.name', 'like', '%' . $this->search . '%');
                           })
                           ->orderBy('users.name')
                           ->select('barcodes.*') // Pilih semua kolom dari barcodes
                           ->paginate(12); // Gunakan pagination

        // Pastikan view yang dirender benar: 'livewire.admin.barcode'
        return view('livewire.admin.barcode', [
            'barcodes' => $barcodes,
        ]);
    }

    /**
     * Confirm deletion of a barcode
     */
    public function confirmDeletion($id, $name)
    {
        $this->selectedId = $id;
        $this->deleteName = $name;
        $this->confirmingDeletion = true;
    }

    /**
     * Cancel deletion confirmation
     */
    public function cancelDeletion()
    {
        $this->confirmingDeletion = false;
        $this->selectedId = null;
        $this->deleteName = null;
    }

    /**
     * Delete the barcode
     */
    public function delete()
    {
        try {
            $barcode = Barcode::findOrFail($this->selectedId);
            $userName = $barcode->user ? $barcode->user->name : 'Unknown User';
            
            $barcode->delete();
            
            $this->banner(__('Barcode for :user deleted successfully.', ['user' => $userName]));
        } catch (\Throwable $th) {
            $this->banner($th->getMessage(), 'danger');
        }

        // Reset properties
        $this->confirmingDeletion = false;
        $this->selectedId = null;
        $this->deleteName = null;
    }


}
