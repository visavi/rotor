@section('header')
    <h1>{{ __('settings.prices') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[sendmoneypoint]') }}">
        <label for="sendmoneypoint" class="form-label">{{ __('settings.points_transfer') }}:</label>
        <input type="number" class="form-control" id="sendmoneypoint" name="sets[sendmoneypoint]" maxlength="4" value="{{ getInput('sets.sendmoneypoint', $settings['sendmoneypoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendmoneypoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editratingpoint]') }}">
        <label for="editratingpoint" class="form-label">{{ __('settings.points_rating_edit') }}:</label>
        <input type="number" class="form-control" id="editratingpoint" name="sets[editratingpoint]" maxlength="4" value="{{ getInput('sets.editratingpoint', $settings['editratingpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editratingpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editforumpoint]') }}">
        <label for="editforumpoint" class="form-label">{{ __('settings.points_topics_edit') }}:</label>
        <input type="number" class="form-control" id="editforumpoint" name="sets[editforumpoint]" maxlength="4" value="{{ getInput('sets.editforumpoint', $settings['editforumpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editforumpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[advertpoint]') }}">
        <label for="advertpoint" class="form-label">{{ __('settings.points_advert_hide') }}:</label>
        <input type="number" class="form-control" id="advertpoint" name="sets[advertpoint]" maxlength="4" value="{{ getInput('sets.advertpoint', $settings['advertpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[advertpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editstatuspoint]') }}">
        <label for="editstatuspoint" class="form-label">{{ __('settings.points_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatuspoint" name="sets[editstatuspoint]" maxlength="4" value="{{ getInput('sets.editstatuspoint', $settings['editstatuspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatuspoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editstatusmoney]') }}">
        <label for="editstatusmoney" class="form-label">{{ __('settings.moneys_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatusmoney" name="sets[editstatusmoney]" maxlength="10" value="{{ getInput('sets.editstatusmoney', $settings['editstatusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatusmoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editcolorpoint]') }}">
        <label for="editcolorpoint" class="form-label">{{ __('settings.points_color_edit') }}:</label>
        <input type="number" class="form-control" id="editcolorpoint" name="sets[editcolorpoint]" maxlength="4" value="{{ getInput('sets.editcolorpoint', $settings['editcolorpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editcolorpoint]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[editcolormoney]') }}">
        <label for="editcolormoney" class="form-label">{{ __('settings.moneys_color_edit') }}:</label>
        <input type="number" class="form-control" id="editcolormoney" name="sets[editcolormoney]" maxlength="10" value="{{ getInput('sets.editcolormoney', $settings['editcolormoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editcolormoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[bonusmoney]') }}">
        <label for="bonusmoney" class="form-label">{{ __('settings.moneys_bonus') }}:</label>
        <input type="number" class="form-control" id="bonusmoney" name="sets[bonusmoney]" maxlength="10" value="{{ getInput('sets.bonusmoney', $settings['bonusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bonusmoney]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[registermoney]') }}">
        <label for="registermoney" class="form-label">{{ __('settings.moneys_registration') }}:</label>
        <input type="number" class="form-control" id="registermoney" name="sets[registermoney]" maxlength="10" value="{{ getInput('sets.registermoney', $settings['registermoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[registermoney]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
