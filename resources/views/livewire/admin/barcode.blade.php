{{-- resources/views/livewire/admin/barcode.blade.php --}}
<div class="p-6 lg:p-8">
  {{-- (Bagian atas file: tombol Buat, Download Semua, Grid Barcode, Modal) --}}
  {{-- ... (Kode HTML tetap sama seperti sebelumnya) ... --}}

  <div class="mb-4 flex flex-wrap items-center gap-2">
    <x-button href="{{ route('admin.barcodes.create') }}" >
      <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5"/>
      Buat QR Code Karyawan
    </x-button>
    <x-secondary-button href="{{ route('admin.barcodes.downloadall') }}">
      <x-heroicon-o-arrow-down-tray class="-ml-1 mr-2 h-5 w-5"/>
      Download Semua QR Code
    </x-secondary-button>
  </div>

  @if ($barcodes->isEmpty())
    <div class="mt-6 rounded border border-yellow-300 bg-yellow-50 p-4 text-center text-yellow-700 dark:border-yellow-700 dark:bg-gray-800 dark:text-yellow-300">
        Belum ada QR Code karyawan yang dibuatc
    </div>
  @else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach ($barcodes as $barcode)
        @if ($barcode->user)
          <div wire:key="barcode-{{ $barcode->id }}"
               class="flex flex-col rounded-lg bg-white p-4 shadow transition duration-150 ease-in-out hover:bg-gray-100 dark:bg-gray-800 dark:shadow-gray-600 hover:dark:bg-gray-700">

            {{-- Info Karyawan --}}
            <h3 class="mb-2 truncate text-center text-lg font-semibold leading-tight text-gray-800 dark:text-white" title="{{ $barcode->user->name }}">
              {{ $barcode->user->name }}
            </h3>
             {{-- Tempat QR Code --}}
             {{-- Beri background putih pada container ini sebagai tambahan jika perlu --}}
            <div class="container mb-3 flex flex-grow items-center justify-center bg-white p-2"> {{-- bg-white ditambahkan --}}
              <div id="qrcode{{ $barcode->id }}" class="h-48 w-48 md:h-56 md:w-56"></div> {{-- Hapus bg-transparent --}}
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-auto flex flex-wrap items-center justify-center gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
              <x-secondary-button href="{{ route('admin.barcodes.download', $barcode->id) }}" title="Download QR Code">
                 <x-heroicon-o-arrow-down-tray class="h-5 w-5"/>
              </x-secondary-button>
              <x-danger-button wire:click="confirmDeletion('{{ $barcode->id }}', '{{ addslashes($barcode->user->name) }}')"
                                 wire:loading.attr="disabled" wire:target="confirmDeletion('{{ $barcode->id }}', '{{ addslashes($barcode->user->name) }}')"
                                 title="Hapus Barcode">
                 <x-heroicon-o-trash class="h-5 w-5"/>
              </x-danger-button>
            </div>

          </div>
        @endif
      @endforeach
    </div>

    {{-- Link Pagination --}}
    @if ($barcodes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $barcodes->links() }}
        </div>
    @endif

  @endif

  {{-- Modal Konfirmasi Hapus --}}
  <x-confirmation-modal wire:model.live="confirmingDeletion">
    <x-slot name="title">Hapus Barcode</x-slot>
    <x-slot name="content">
      Apakah Anda yakin ingin menghapus QR Code untuk karyawan <b>{{ $deleteName ?? '' }}</b>? Tindakan ini tidak dapat dibatalkan.
    </x-slot>
    <x-slot name="footer">
      <x-secondary-button wire:click="$set('confirmingDeletion', false)" wire:loading.attr="disabled">
        {{ __('Cancel') }}
      </x-secondary-button>
      <x-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
        {{ __('Confirm Delete') }}
      </x-danger-button>
    </x-slot>
  </x-confirmation-modal>
</div>

{{-- Bagian @push('scripts') dengan perbaikan warna QR Code --}}
@push('scripts')
<script src="{{ url('/assets/js/qrcode.min.js') }}"></script>
<script>
  (function() {
    // --- PERBAIKAN DI SINI ---
    // Fungsi generate QR code dengan warna tetap (hitam di atas putih)
    function generateQRCode(elId, text) { // Hapus parameter isDarkMode
      const element = document.getElementById(elId);
      if (!element) { return; }
      element.innerHTML = '';
      const options = {
        text: text,
        width: element.offsetWidth || 224, // Sesuaikan ukuran default jika perlu
        height: element.offsetHeight || 224,
        colorDark: "#000000", // Warna QR Code selalu hitam
        colorLight: "#ffffff", // Warna background QR Code selalu putih
        correctLevel: QRCode.CorrectLevel.M
      };
      try { new QRCode(element, options); } catch (error) {
        console.error(`Failed to generate QR Code for ${elId}:`, error);
        element.innerHTML = '<p class="text-xs text-red-500 text-center">Gagal<br>memuat<br>QR Code</p>';
      }
    }
    // --- AKHIR PERBAIKAN WARNA ---

    // Fungsi untuk merender semua QR code yang ada di DOM
    function renderAllQRCodes() {
      const barcodeElements = document.querySelectorAll('[id^="qrcode"]');
      if (barcodeElements.length === 0) return;

      // Hapus logika deteksi dark mode karena warna sudah tetap
      // const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

      const initialBarcodeData = @json(
          (isset($barcodes) && ($barcodes instanceof \Illuminate\Support\Collection || $barcodes instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $barcodes : collect([])) // Handle koleksi atau paginator
          ->filter(fn($b) => $b->user !== null)
          ->mapWithKeys(fn($b) => [$b->id => $b->value])
          ->all()
      );
      barcodeElements.forEach(element => {
        const barcodeId = element.id.replace('qrcode', '');
        const barcodeValue = initialBarcodeData[barcodeId];
        if (barcodeValue) {
          // Panggil generateQRCode tanpa parameter isDarkMode
          generateQRCode(element.id, barcodeValue);
        }
      });
    }

    // Render saat load awal
    document.addEventListener('livewire:init', renderAllQRCodes);

    // Hapus listener untuk perubahan dark mode karena tidak perlu re-render warna lagi
    // if (window.matchMedia) {
    //   window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', renderAllQRCodes);
    // }

    // Re-render setelah Livewire update DOM (misalnya setelah delete atau pindah halaman pagination)
    document.addEventListener('livewire:navigated', renderAllQRCodes);

  })();
</script>
@endpush
