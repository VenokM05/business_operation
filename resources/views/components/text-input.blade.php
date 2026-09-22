@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-line rounded-lg shadow-sm text-ink placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/25 disabled:bg-canvas disabled:text-muted']) !!}>
