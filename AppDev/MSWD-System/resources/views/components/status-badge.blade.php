@props(['status'])

@php
    $normalized = strtolower($status);
    $class = match (true) {
        str_contains($normalized, 'verified'),
        str_contains($normalized, 'released'),
        str_contains($normalized, 'active'),
        str_contains($normalized, 'eligible'),
        str_contains($normalized, 'success'),
        str_contains($normalized, 'ready')
            => 'status-success',
        str_contains($normalized, 'pending'),
        str_contains($normalized, 'review'),
        str_contains($normalized, 'draft')
            => 'status-warning',
        str_contains($normalized, 'rejected'),
        str_contains($normalized, 'blocked'),
        str_contains($normalized, 'already')
            => 'status-danger',
        default => 'status-neutral',
    };
@endphp

<span class="status-badge {{ $class }}">{{ $status }}</span>
