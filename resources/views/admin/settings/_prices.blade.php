@section('header')
    <h1>{{ __('settings.prices') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[advertpoint]') }}">
        <label for="advertpoint" class="form-label">{{ __('settings.points_advert_hide') }}:</label>
        <input type="number" class="form-control" id="advertpoint" name="sets[advertpoint]" maxlength="4" value="{{ old('sets.advertpoint', $settings['advertpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[advertpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editstatuspoint]') }}">
        <label for="editstatuspoint" class="form-label">{{ __('settings.points_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatuspoint" name="sets[editstatuspoint]" maxlength="4" value="{{ old('sets.editstatuspoint', $settings['editstatuspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatuspoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editstatusmoney]') }}">
        <label for="editstatusmoney" class="form-label">{{ __('settings.moneys_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatusmoney" name="sets[editstatusmoney]" maxlength="10" value="{{ old('sets.editstatusmoney', $settings['editstatusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatusmoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editcolorpoint]') }}">
        <label for="editcolorpoint" class="form-label">{{ __('settings.points_color_edit') }}:</label>
        <input type="number" class="form-control" id="editcolorpoint" name="sets[editcolorpoint]" maxlength="4" value="{{ old('sets.editcolorpoint', $settings['editcolorpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editcolorpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editcolormoney]') }}">
        <label for="editcolormoney" class="form-label">{{ __('settings.moneys_color_edit') }}:</label>
        <input type="number" class="form-control" id="editcolormoney" name="sets[editcolormoney]" maxlength="10" value="{{ old('sets.editcolormoney', $settings['editcolormoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editcolormoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[bonusmoney]') }}">
        <label for="bonusmoney" class="form-label">{{ __('settings.moneys_bonus') }}:</label>
        <input type="number" class="form-control" id="bonusmoney" name="sets[bonusmoney]" maxlength="10" value="{{ old('sets.bonusmoney', $settings['bonusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bonusmoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[registermoney]') }}">
        <label for="registermoney" class="form-label">{{ __('settings.moneys_registration') }}:</label>
        <input type="number" class="form-control" id="registermoney" name="sets[registermoney]" maxlength="10" value="{{ old('sets.registermoney', $settings['registermoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[registermoney]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
