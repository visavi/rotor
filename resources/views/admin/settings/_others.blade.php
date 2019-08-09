@section('header')
    <h1>{{ trans('settings.others') }}</h1>
@stop

<form action="/admin/settings?act=others" method="post">
    @csrf
    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[errorlog]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[errorlog]" id="errorlog"{{ getInput('sets.errorlog', $settings['errorlog']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="errorlog">Включить запись логов</label>
    </div>

    <div class="form-group{{ hasError('sets[keywords]') }}">
        <label for="keywords">Ключевые слова (keywords):</label>
        <input type="text" class="form-control" id="keywords" name="sets[keywords]" maxlength="250" value="{{ getInput('sets.keywords', $settings['keywords']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[keywords]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[description]') }}">
        <label for="description">Краткое описание (description):</label>
        <input type="text" class="form-control" id="description" name="sets[description]" maxlength="250" value="{{ getInput('sets.description', $settings['description']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[description]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[nocheck]') }}">
        <label for="nocheck">Не сканируемые расширения (через запятую):</label>
        <input type="text" class="form-control" id="nocheck" name="sets[nocheck]" maxlength="100" value="{{ getInput('sets.nocheck', $settings['nocheck']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[nocheck]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[moneyname]') }}">
        <label for="moneyname">Название игровой валюты:</label>
        <input type="text" class="form-control" id="moneyname" name="sets[moneyname]" maxlength="100" value="{{ getInput('sets.moneyname', $settings['moneyname']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[moneyname]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[scorename]') }}">
        <label for="scorename">Название баллов:</label>
        <input type="text" class="form-control" id="scorename" name="sets[scorename]" maxlength="100" value="{{ getInput('sets.scorename', $settings['scorename']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[scorename]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[statusdef]') }}">
        <label for="statusdef">Статус по умолчанию:</label>
        <input type="text" class="form-control" id="statusdef" name="sets[statusdef]" maxlength="20" value="{{ getInput('sets.statusdef', $settings['statusdef']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[statusdef]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guestsuser]') }}">
        <label for="guestsuser">Неавторизованный пользователь:</label>
        <input type="text" class="form-control" id="guestsuser" name="sets[guestsuser]" maxlength="20" value="{{ getInput('sets.guestsuser', $settings['guestsuser']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[guestsuser]') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[addbansend]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[addbansend]" id="addbansend"{{ getInput('sets.addbansend', $settings['addbansend']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="addbansend">Объяснение из бана</label>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
