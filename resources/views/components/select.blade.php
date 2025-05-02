@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
  'class' =>
  'w-full h-10 px-3 border border-blue-300 text-blue-500 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50',
  ]) !!}>
  {{ $slot }}
</select>