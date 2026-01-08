{{-- Field Select Component --}}
@props(['label', 'name', 'options' => [], 'value' => null, 'required' => false, 'help' => '', 'error' => null])

<div class="form-group">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="required-field">*</span>
        @endif
    </label>
    <select 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="form-control form-select @error($name) is-invalid @enderror"
        @if($required) required @endif
    >
        <option value="">Pilih {{ $label }}</option>
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ old($name, $value) == $optValue ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
