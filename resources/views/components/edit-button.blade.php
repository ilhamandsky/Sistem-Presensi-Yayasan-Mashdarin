<!-- resources/views/components/edit-button.blade.php -->
@php
    $class = 'bg-yellow-400 hover:bg-yellow-500 text-white flex items-center gap-1 px-3 py-1 rounded text-sm';
@endphp

@if (!isset($attributes['href']))
    <button {{ $attributes->merge(['type' => 'button', 'class' => $class]) }}>
        <x-heroicon-o-pencil class="h-3.5 w-3.5" />
        Edit
    </button>
@else
    <a {{ $attributes->merge(['class' => $class]) }}>
        <x-heroicon-o-pencil class="h-3.5 w-3.5" />
        Edit
    </a>
@endif
