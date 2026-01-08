{{-- Form Section Header --}}
@props(['title', 'subtitle' => null])

<div class="form-section">
    <h5 class="form-section-title">
        {{ $title }}
        @if($subtitle)
            <small class="d-block text-muted mt-1" style="font-size: 0.75rem; text-transform: none; letter-spacing: normal;">{{ $subtitle }}</small>
        @endif
    </h5>
    {{ $slot }}
</div>
