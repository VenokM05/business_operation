@props(['active' => false, 'href'])

@php
$classes = $active ? 'side-link side-link-active' : 'side-link';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
