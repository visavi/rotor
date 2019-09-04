@section('header')
    <h1>{{ __('settings.prices') }}</h1>
@stop

<form action="/admin/settings?act=prices" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[sendmoneypoint]') }}">
        <label for="sendmoneypoint">{{ __('settings.points_transfer') }}:</label>
        <input type="number" class="form-control" id="sendmoneypoint" name="sets[sendmoneypoint]" maxlength="4" value="{{ getInput('sets.sendmoneypoint', $settings['sendmoneypoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendmoneypoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[editratingpoint]') }}">
        <label for="editratingpoint">{{ __('settings.points_rating_edit') }}:</label>
        <input type="number" class="form-control" id="editratingpoint" name="sets[editratingpoint]" maxlength="4" value="{{ getInput('sets.editratingpoint', $settings['editratingpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editratingpoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[editforumpoint]') }}">
        <label for="editforumpoint">{{ __('settings.points_topics_edit') }}:</label>
        <input type="number" class="form-control" id="editforumpoint" name="sets[editforumpoint]" maxlength="4" value="{{ getInput('sets.editforumpoint', $settings['editforumpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editforumpoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[advertpoint]') }}">
        <label for="advertpoint">{{ __('settings.points_advert_hide') }}:</label>
        <input type="number" class="form-control" id="advertpoint" name="sets[advertpoint]" maxlength="4" value="{{ getInput('sets.advertpoint', $settings['advertpoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[advertpoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[editstatuspoint]') }}">
        <label for="editstatuspoint">{{ __('settings.points_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatuspoint" name="sets[editstatuspoint]" maxlength="4" value="{{ getInput('sets.editstatuspoint', $settings['editstatuspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatuspoint]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[editstatusmoney]') }}">
        <label for="editstatusmoney">{{ __('settings.moneys_status_edit') }}:</label>
        <input type="number" class="form-control" id="editstatusmoney" name="sets[editstatusmoney]" maxlength="10" value="{{ getInput('sets.editstatusmoney', $settings['editstatusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[editstatusmoney]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[bonusmoney]') }}">
        <label for="bonusmoney">{{ __('settings.moneys_bonus') }}:</label>
        <input type="number" class="form-control" id="bonusmoney" name="sets[bonusmoney]" maxlength="10" value="{{ getInput('sets.bonusmoney', $settings['bonusmoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bonusmoney]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[registermoney]') }}">
        <label for="registermoney">{{ __('settings.moneys_registration') }}:</label>
        <input type="number" class="form-control" id="registermoney" name="sets[registermoney]" maxlength="10" value="{{ getInput('sets.registermoney', $settings['registermoney']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[registermoney]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
