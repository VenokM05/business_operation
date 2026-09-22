@props(['record', 'resource', 'label'])
@if ($record->trashed())
    <span>{{ $label }}</span>
    @can('restore', $record)
        <form method="POST" action="{{ route($resource.'.restore', $record) }}" class="mt-2">
            @csrf @method('PATCH')
            <button class="text-link text-xs" aria-label="Restore {{ $label }}">Restore</button>
        </form>
    @endcan
@else
    <a href="{{ route($resource.'.show', $record) }}" class="text-link">{{ $label }}</a>
@endif
