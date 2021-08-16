<form method="post">
    @csrf
    <div class="mb-3{{ hasError('type') }}">
        <label for="type" class="form-label">{{ __('admin.user_fields.type') }}:</label>

        <?php $inputType = getInput('type', $field->type); ?>
        <select class="form-select" name="type" id="type">
            @foreach ($types as $type)
                <?php $selected = ($type === $inputType) ? ' selected' : ''; ?>
                <option value="{{ $type }}"{{ $selected }}>{{ __('admin.paid_adverts.' . $type) }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('type') }}</div>
    </div>

    <div class="mb-3{{ hasError('name') }}">
        <label for="name" class="form-label">{{ __('admin.user_fields.name') }}:</label>
        <input type="text" name="name" class="form-control" id="name" maxlength="50" value="{{ getInput('name', $field->name) }}">
        <div class="invalid-feedback">{{ textError('name') }}</div>
    </div>

    <div class="mb-3{{ hasError('rule') }}">
        <label for="name" class="form-label">{{ __('admin.user_fields.rule') }}:</label>
        <input type="text" name="rule" class="form-control" id="rule" maxlength="100" value="{{ getInput('rule', $field->rule) }}">
        <div class="invalid-feedback">{{ textError('rule') }}</div>
    </div>

    <div class="mb-3{{ hasError('sort') }}">
        <label for="sort" class="form-label">{{ __('main.position') }}:</label>
        <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $field->sort ?? 0) }}" required>
        <div class="invalid-feedback">{{ textError('sort') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
