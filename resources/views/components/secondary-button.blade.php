@php
$class =
    'inline-flex items-center px-4 py-2 bg-white border border-[#638CF8] rounded-md font-semibold text-xs text-[#638CF8] shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#638CF8] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150';
@endphp

@if (!isset($attributes['href']))
<button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>
  {{ $slot }}
</button>
@else
<a {{ $attributes->merge(['class' => $class]) }}>
  {{ $slot }}
</a>
@endif