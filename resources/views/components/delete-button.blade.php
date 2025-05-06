<!-- resources/views/components/delete-button.blade.php -->
@php
    $class = 'bg-red-500 hover:bg-red-600 text-white flex items-center gap-1 px-3 py-1 rounded text-sm';
@endphp

@if (!isset($attributes['href']))
    <button {{ $attributes->merge(['type' => 'button', 'class' => $class]) }}>
        <x-heroicon-o-trash class="h-3.5 w-3.5" />
        Hapus
    </button>
@else
    <a {{ $attributes->merge(['class' => $class]) }}>
        <x-heroicon-o-trash class="h-3.5 w-3.5" />
        Hapus
    </a>
@endif
