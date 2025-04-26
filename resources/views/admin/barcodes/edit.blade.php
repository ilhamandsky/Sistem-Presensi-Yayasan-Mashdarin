<x-app-layout>
  {{-- HAPUS pushOnce untuk Leaflet --}}

  <x-slot name="header">
    {{-- Judul disesuaikan untuk edit nama barcode user --}}
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{-- Gunakan optional chaining (?->) untuk keamanan jika user relasi null --}}
      {{ __('Edit Barcode Name for :user', ['user' => $barcode->user?->name ?? 'Unknown User']) }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 lg:p-8">
          {{-- Pastikan route dan method benar --}}
          <form action="{{ route('admin.barcodes.update', $barcode->id) }}" method="post">
            @csrf
            {{-- Gunakan PUT atau PATCH untuk update --}}
            @method('PUT')

            {{-- 1. Tampilkan Informasi User (Read Only) --}}
            <div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
              <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Informasi Karyawan</h3>
              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                  <div>
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama:</span>
                      <p class="text-gray-800 dark:text-gray-200">{{ $barcode->user?->name ?? 'N/A' }}</p>
                  </div>
                  <div>
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Value (User ID):</span>
                      {{-- Tampilkan value barcode (User ID) --}}
                      <p class="truncate text-gray-800 dark:text-gray-200" title="{{ $barcode->value }}">{{ $barcode->value }}</p>
                  </div>
              </div>
              <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Informasi karyawan dan value barcode tidak dapat diubah di sini.</p>
            </div>

            {{-- 2. Input untuk Nama Barcode (Alias) --}}
            <div class="w-full">
              <x-label for="name">Nama/Alias Barcode <span class="text-red-500">*</span></x-label>
              {{-- Gunakan old() dengan fallback ke $barcode->name --}}
              <x-input name="name" id="name" class="mt-1 block w-full" type="text" :value="old('name', $barcode->name)" required
                placeholder="Nama Alias Barcode (cth: QR Ahmad Fauzi)" />
              {{-- Pastikan error directive benar --}}
              @error('name')
                <x-input-error for="name" class="mt-2" :message="$message" />
              @enderror
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nama ini hanya untuk referensi admin dan penamaan file download.</p>
            </div>

            {{-- HAPUS SEMUA Input untuk Value, Radius, Latitude, Longitude, dan Map --}}

            {{-- 3. Tombol Aksi --}}
            <div class="mb-3 mt-6 flex items-center justify-end gap-4"> {{-- Beri jarak antar tombol --}}
              {{-- Tombol Cancel untuk kembali ke halaman index --}}
              <x-secondary-button type="button" onclick="window.location='{{ route('admin.barcodes') }}'">
                {{ __('Cancel') }}
              </x-secondary-button>
              {{-- Tombol Save --}}
              <x-button type="submit">
                {{ __('Save Changes') }}
              </x-button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- HAPUS pushOnce untuk script Leaflet --}}
</x-app-layout>
