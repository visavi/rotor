<div class="mb-3">
    <label for="type" class="form-label">{{ __('admin.user_fields.type') }}:</label>

    <?php $inputType = old('type', $field->type); ?>
    <select class="form-select{{ hasError('type') }}" name="type" id="type">
        @foreach ($types as $type)
            <?php $selected = ($type === $inputType) ? ' selected' : ''; ?>
            <option value="{{ $type }}"{{ $selected }}>{{ __('admin.paid_adverts.' . $type) }}</option>
        @endforeach
    </select>

    <div class="invalid-feedback">{{ textError('type') }}</div>
</div>

<div class="mb-3">
    <label for="name" class="form-label">{{ __('admin.user_fields.name') }}:</label>
    <input type="text" name="name" class="form-control{{ hasError('name') }}" id="name" maxlength="50" value="{{ old('name', $field->name) }}">
    <div class="invalid-feedback">{{ textError('name') }}</div>
</div>

<div class="mb-3">
    <label for="sort" class="form-label">{{ __('main.position') }}:</label>
    <input type="number" name="sort" class="form-control{{ hasError('sort') }}" id="sort" maxlength="2" value="{{ old('sort', $field->sort ?? 0) }}" required>
    <div class="invalid-feedback">{{ textError('sort') }}</div>
</div>

<div class="mb-3">
    <label for="length" class="form-label">{{ __('admin.user_fields.length') }}:</label>
    <input type="number" name="length" class="form-control{{ hasError('length') }}" id="length" value="{{ old('length', $field->length) }}">
    <div class="invalid-feedback">{{ textError('length') }}</div>
</div>

<div class="form-check">
    <input type="hidden" value="0" name="required">
    <input type="checkbox" class="form-check-input" value="1" name="required" id="required"{{ old('required', $field->required) ? ' checked' : '' }}>
    <label class="form-check-label" for="required">{{ __('admin.user_fields.required') }}</label>
</div>

<button class="btn btn-primary">{{ __('main.save') }}</button>

