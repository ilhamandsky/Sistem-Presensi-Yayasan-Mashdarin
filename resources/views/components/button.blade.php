@php
$class =
    'inline-flex items-center px-4 py-2 bg-[#638CF8] border border-transparent rounded-md font-semibold text-xs text-white hover:bg-[#507de8] focus:bg-[#507de8] active:bg-[#3c6fe0] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150';
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