@section('header')
    <h1>{{ __('settings.others') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-check">
        <input type="hidden" value="0" name="sets[errorlog]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[errorlog]" id="errorlog"{{ getInput('sets.errorlog', $settings['errorlog']) ? ' checked' : '' }}>
        <label for="errorlog" class="form-check-label">{{ __('settings.log_enable') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[description]') }}">
        <label for="description" class="form-label">{{ __('settings.description') }}:</label>
        <input type="text" class="form-control" id="description" name="sets[description]" maxlength="250" value="{{ getInput('sets.description', $settings['description']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[description]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[nocheck]') }}">
        <label for="nocheck" class="form-label">{{ __('settings.unscannable_ext') }}:</label>
        <input type="text" class="form-control" id="nocheck" name="sets[nocheck]" maxlength="100" value="{{ getInput('sets.nocheck', $settings['nocheck']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[nocheck]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[moneyname]') }}">
        <label for="moneyname" class="form-label">{{ __('settings.moneys') }}:</label>
        <input type="text" class="form-control" id="moneyname" name="sets[moneyname]" maxlength="100" value="{{ getInput('sets.moneyname', $settings['moneyname']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[moneyname]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[scorename]') }}">
        <label for="scorename" class="form-label">{{ __('settings.points') }}:</label>
        <input type="text" class="form-control" id="scorename" name="sets[scorename]" maxlength="100" value="{{ getInput('sets.scorename', $settings['scorename']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[scorename]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[statusdef]') }}">
        <label for="statusdef" class="form-label">{{ __('settings.default_status') }}:</label>
        <input type="text" class="form-control" id="statusdef" name="sets[statusdef]" maxlength="20" value="{{ getInput('sets.statusdef', $settings['statusdef']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[statusdef]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[guestsuser]') }}">
        <label for="guestsuser" class="form-label">{{ __('settings.guestsuser') }}:</label>
        <input type="text" class="form-control" id="guestsuser" name="sets[guestsuser]" maxlength="20" value="{{ getInput('sets.guestsuser', $settings['guestsuser']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guestsuser]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[deleted_user]') }}">
        <label for="deleted_user" class="form-label">{{ __('settings.deleted_user') }}:</label>
        <input type="text" class="form-control" id="deleted_user" name="sets[deleted_user]" maxlength="20" value="{{ getInput('sets.deleted_user', $settings['deleted_user']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[deleted_user]') }}</div>
    </div>

    <div class="form-check">
        <input type="hidden" value="0" name="sets[addbansend]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[addbansend]" id="addbansend"{{ getInput('sets.addbansend', $settings['addbansend']) ? ' checked' : '' }}>
        <label for="addbansend" class="form-check-label">{{ __('settings.ban_explanation') }}</label>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
