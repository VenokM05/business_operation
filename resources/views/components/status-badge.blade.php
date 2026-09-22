@props(['status'])
@php
    $variant = match ($status?->value) {
        'completed', 'resolved', 'closed', 'active' => 'badge-success',
        'in_progress', 'assigned' => 'badge-brand',
        'pending', 'on_hold', 'high', 'urgent' => 'badge-warning',
        'cancelled' => 'badge-danger',
        default => 'badge-neutral',
    };
@endphp
<span class="{{ $variant }}">{{ $status?->label() ?? '—' }}</span>
