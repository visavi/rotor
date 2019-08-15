@section('header')
    <h1>{{ trans('settings.others') }}</h1>
@stop

<form action="/admin/settings?act=others" method="post">
    @csrf
    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[errorlog]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[errorlog]" id="errorlog"{{ getInput('sets.errorlog', $settings['errorlog']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="errorlog">{{ trans('settings.log_enable') }}</label>
    </div>

    <div class="form-group{{ hasError('sets[description]') }}">
        <label for="description">{{ trans('settings.description') }}:</label>
        <input type="text" class="form-control" id="description" name="sets[description]" maxlength="250" value="{{ getInput('sets.description', $settings['description']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[description]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[nocheck]') }}">
        <label for="nocheck">{{ trans('settings.unscannable_ext') }}:</label>
        <input type="text" class="form-control" id="nocheck" name="sets[nocheck]" maxlength="100" value="{{ getInput('sets.nocheck', $settings['nocheck']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[nocheck]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[moneyname]') }}">
        <label for="moneyname">{{ trans('settings.moneys') }}:</label>
        <input type="text" class="form-control" id="moneyname" name="sets[moneyname]" maxlength="100" value="{{ getInput('sets.moneyname', $settings['moneyname']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[moneyname]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[scorename]') }}">
        <label for="scorename">{{ trans('settings.points') }}:</label>
        <input type="text" class="form-control" id="scorename" name="sets[scorename]" maxlength="100" value="{{ getInput('sets.scorename', $settings['scorename']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[scorename]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[statusdef]') }}">
        <label for="statusdef">{{ trans('settings.default_status') }}:</label>
        <input type="text" class="form-control" id="statusdef" name="sets[statusdef]" maxlength="20" value="{{ getInput('sets.statusdef', $settings['statusdef']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[statusdef]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guestsuser]') }}">
        <label for="guestsuser">{{ trans('settings.guestsuser') }}:</label>
        <input type="text" class="form-control" id="guestsuser" name="sets[guestsuser]" maxlength="20" value="{{ getInput('sets.guestsuser', $settings['guestsuser']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guestsuser]') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[addbansend]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[addbansend]" id="addbansend"{{ getInput('sets.addbansend', $settings['addbansend']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="addbansend">{{ trans('settings.ban_explanation') }}</label>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
