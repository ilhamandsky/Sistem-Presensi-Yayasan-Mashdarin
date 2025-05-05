<?php

namespace App\Http\Controllers\Admin;

use App\BarcodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\User; // Import User model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarcodeController extends Controller
{
    // Hapus rules lama atau sesuaikan
    // protected $rules = [ ... ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // View index tetap sama, logika ada di Livewire Component
        return view('admin.barcodes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil user yang BELUM memiliki barcode
        $users = User::whereDoesntHave('barcode')
                     ->orderBy('name')
                     ->pluck('name', 'id'); // Ambil 'name' sebagai teks, 'id' sebagai value

        // Kirim daftar user ke view
        return view('admin.barcodes.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi hanya user_id yang dipilih
        $request->validate([
            'user_id' => [
                'required',
                 Rule::exists('users', 'id'), // Pastikan user ada
                 Rule::unique('barcodes', 'user_id') // Pastikan user belum punya barcode
            ],
            // Hapus validasi untuk name, value, lat, lng, radius
        ]);

        try {
            // Cari user yang dipilih
            $user = User::findOrFail($request->user_id);

            // Buat barcode baru
            Barcode::create([
                'user_id' => $user->id,
                'name' => $user->name, // Isi nama barcode dengan nama user
                'value' => $user->id, // Isi value barcode dengan ID user (ULID)
                // Kolom lat, lng, radius biarkan null (default dari DB)
            ]);

            return redirect()->route('admin.barcodes')->with('flash.banner', __('QR Code untuk :user berhasil dibuat.', ['user' => $user->name]));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withInput() // Kembalikan input sebelumnya
                ->with('flash.banner', $th->getMessage())
                ->with('flash.bannerStyle', 'danger');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * Note: Mengedit barcode user mungkin tidak umum, biasanya dihapus lalu dibuat baru.
     * Contoh ini hanya memungkinkan edit 'name' barcode (jika diperlukan, misal alias).
     */
    public function edit(Barcode $barcode)
    {
         // Load relasi user agar bisa ditampilkan di view
         $barcode->load('user');
         return view('admin.barcodes.edit', compact('barcode'));
    }

    /**
     * Update the specified resource in storage.
     * Hanya update 'name' barcode. Relasi user tidak diubah.
     */
    public function update(Request $request, Barcode $barcode)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Hapus validasi lain
        ]);

        try {
            $barcode->update([
                'name' => $request->name,
                // Jangan update user_id atau value
            ]);
            // Load user name untuk pesan sukses
            $userName = $barcode->user ? $barcode->user->name : 'Unknown User';
            return redirect()->route('admin.barcodes')->with('flash.banner', __('Barcode name for :user updated successfully.', ['user' => $userName]));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withInput()
                ->with('flash.banner', $th->getMessage())
                ->with('flash.bannerStyle', 'danger');
        }
    }


    /**
     * Download QR Code for a specific user barcode.
     */
    public function download($barcodeId)
    {
        // Eager load relasi user
        $barcode = Barcode::with('user')->findOrFail($barcodeId);

        // Pastikan user ada
        if (!$barcode->user) {
             return redirect()->back()
                ->with('flash.banner', __('User associated with this barcode not found.'))
                ->with('flash.bannerStyle', 'danger');
        }

        // Generate QR code menggunakan 'value' (yang berisi user->id)
        $barcodeFile = (new BarcodeGenerator(width: 1280, height: 1280))
                        ->generateQrCode($barcode->value); // Gunakan value (user->id)

        // Gunakan nama user (atau NIP) untuk nama file, bersihkan karakter tidak valid
        $filename = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $barcode->user->name ?? $barcode->value) . '.png';

        return response($barcodeFile)->withHeaders([
            'Content-Type' => 'application/octet-stream', // Atau 'image/png'
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download all user barcodes as a zip file.
     */
    public function downloadAll()
    {
        // Ambil semua barcode dengan relasi user
        $barcodes = Barcode::with('user')->get();

        // Filter barcode yang memiliki user valid
        $validBarcodes = $barcodes->filter(fn($barcode) => $barcode->user !== null);

        if ($validBarcodes->isEmpty()) {
            return redirect()->back()
                ->with('flash.banner', __('No valid user barcodes found to download.'))
                ->with('flash.bannerStyle', 'danger');
        }

        // Buat array [nama_file => value_qrcode]
        // Gunakan NIP jika ada dan unik, jika tidak gunakan nama + ID user
        $valuesForZip = $validBarcodes->mapWithKeys(function ($barcode) {
             // Prioritaskan NIP jika ada dan tidak kosong
             $baseName = !empty($barcode->user->nip) ? $barcode->user->nip : $barcode->user->name . '_' . $barcode->user->id;
             // Bersihkan nama file
             $fileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $baseName);
             return [$fileName => $barcode->value]; // Nama file => User ID
        })->toArray();

        // Generate zip file
        $zipFile = (new BarcodeGenerator(width: 1280, height: 1280))
                    ->generateQrCodesZip($valuesForZip);

        // Kirim response download
        return response()->download($zipFile, 'user_barcodes.zip')->deleteFileAfterSend(true); // Hapus file zip setelah didownload
    }

    // Method show() tidak digunakan di contoh ini, bisa dihapus atau diimplementasikan jika perlu
    // Method destroy() akan ditangani oleh Livewire Component
}
