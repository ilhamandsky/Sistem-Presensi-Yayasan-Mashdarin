<x-app-layout>
  {{-- Hapus @pushOnce('styles') untuk Leaflet jika tidak dipakai lagi --}}

  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Buat QR Code Karyawan') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
        <div class="p-6 lg:p-8">
          <form action="{{ route('admin.barcodes.store') }}" method="post">
            @csrf

            {{-- Ganti input field dengan dropdown user --}}
            <div class="w-full">
              <x-label for="user_id">Pilih Karyawan</x-label>
              {{-- Gunakan Select Input Component jika ada, atau tag <select> biasa --}}
              <x-select name="user_id" id="user_id">
                <option value="" disabled selected>Pilih Karyawan</option>
                @foreach ($users as $id => $name)
                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>
                  {{ $name }}
                </option>
                @endforeach
              </x-select>
              @error('user_id')
              <x-input-error for="user_id" class="mt-2" message="{{ $message }}" />
              @enderror
            </div>

            {{-- Hapus Input untuk Name, Value, Radius, Koordinat, dan Peta --}}
            {{-- <div class="flex flex-col gap-4 md:flex-row md:items-start md:gap-3"> ... </div> --}}
            {{-- <div class="mt-4 flex gap-3"> ... </div> --}}
            {{-- <div class="mt-5"> ... </div> --}}
            {{-- Tombol Tampilkan Peta dan div#map dihapus --}}


            <div class="mb-3 mt-6 flex items-center justify-end"> {{-- Beri margin atas --}}
              <x-button class="ms-4">
                <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5" />
                {{ __('Buat QR Code Karyawan') }} {{-- Ubah teks tombol --}}
              </x-button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Hapus @pushOnce('scripts') untuk Leaflet jika tidak dipakai --}}
</x-app-layout>