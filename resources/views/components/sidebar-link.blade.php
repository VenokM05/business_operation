@props(['active' => false, 'href'])

@php
$classes = $active
    ? 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium bg-indigo-50 text-indigo-700'
    : 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
