<x-app-layout>
  {{-- Hapus @pushOnce('styles') untuk Leaflet jika tidak dipakai lagi --}}

  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Create New User Barcode') }}
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
              <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600">
                <option value="">-- Pilih Karyawan --</option>
                @foreach ($users as $id => $name)
                  <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>
                    {{ $name }}
                  </option>
                @endforeach
              </select>
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
                {{ __('Create Barcode') }} {{-- Ubah teks tombol --}}
              </x-button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Hapus @pushOnce('scripts') untuk Leaflet jika tidak dipakai --}}
</x-app-layout>
