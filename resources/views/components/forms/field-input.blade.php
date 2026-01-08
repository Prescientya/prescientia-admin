{{-- Field Input Component --}}
@props(['label', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'placeholder' => '', 'help' => '', 'error' => null])

<div class="form-group">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="required-field">*</span>
        @endif
    </label>
    <input 
        type="{{ $type }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="form-control @error($name) is-invalid @enderror" 
        value="{{ old($name, $value ?? '') }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
    >
    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
