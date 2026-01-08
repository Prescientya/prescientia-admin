{{-- Field File Component --}}
@props(['label', 'name', 'accept' => 'image/*', 'required' => false, 'help' => '', 'error' => null, 'preview' => null, 'previewLabel' => null])

<div class="form-group">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="required-field">*</span>
        @endif
    </label>
    
    @if($preview)
        <div class="photo-preview-container">
            <img src="{{ $preview }}" alt="Preview" class="photo-preview">
            <p class="photo-preview-label">{{ $previewLabel ?? 'File saat ini' }}</p>
        </div>
    @endif
    
    <input 
        type="file" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="form-control @error($name) is-invalid @enderror" 
        accept="{{ $accept }}"
        @if($required) required @endif
    >
    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
    @if($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
