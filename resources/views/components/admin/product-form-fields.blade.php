@props(['fields', 'model' => null])

@foreach($fields as $field)
    @php
        $name = $field['key'];
        $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $name));
        $type = $field['type'] ?? 'text';
        $options = $field['options'] ?? [];
        $required = $field['required'] ?? false;
        $step = $field['step'] ?? null;
        $value = old($name, $model?->{$name} ?? ($field['default'] ?? ''));
    @endphp

    <div class="mb-3">
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>

        @if($type === 'textarea')
            <textarea class="form-control @error($name) is-invalid @enderror"
                      id="{{ $name }}"
                      name="{{ $name }}"
                      rows="4"
                      {{ $required ? 'required' : '' }}>{{ $value }}</textarea>
        @elseif($type === 'select')
            <select class="form-control @error($name) is-invalid @enderror"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    {{ $required ? 'required' : '' }}>
                @foreach($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($type === 'file')
            <input type="file"
                   class="form-control @error($name) is-invalid @enderror"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   accept="image/*"
                   {{ $required ? 'required' : '' }}>
            @if($model && $model->{$name})
                <div class="mt-2">
                    <small class="text-muted d-block">Current image:</small>
                    <img src="{{ asset('storage/' . $model->{$name}) }}" alt="image" class="img-thumbnail" style="max-height: 120px;">
                </div>
            @endif
        @else
            <input type="{{ $type }}"
                   class="form-control @error($name) is-invalid @enderror"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   value="{{ $type === 'file' ? '' : $value }}"
                   @if($step) step="{{ $step }}" @endif
                   {{ $required ? 'required' : '' }}>
        @endif

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endforeach
