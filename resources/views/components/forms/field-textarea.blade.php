{{-- Field Textarea Component --}}
@props(['label', 'name', 'value' => null, 'rows' => 3, 'required' => false, 'placeholder' => '', 'help' => '', 'error' => null])

<div class="form-group">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="required-field">*</span>
        @endif
    </label>
    <textarea 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="form-control @error($name) is-invalid @enderror" 
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
    >{{ old($name, $value ?? '') }}</textarea>
    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
