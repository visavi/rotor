<h3>Прочее / Другое</h3>

<form action="/admin/settings?act=other" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[errorlog]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[errorlog]" id="errorlog"{{ getInput('sets.errorlog', $settings['errorlog']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="errorlog">Включить запись логов</label>
    </div>

    <div class="form-group{{ hasError('sets[keywords]') }}">
        <label for="keywords">Ключевые слова (keywords):</label>
        <input type="text" class="form-control" id="keywords" name="sets[keywords]" maxlength="250" value="{{ getInput('sets.keywords', $settings['keywords']) }}" required>
        {!! textError('sets[keywords]') !!}
    </div>

    <div class="form-group{{ hasError('sets[description]') }}">
        <label for="description">Краткое описание (description):</label>
        <input type="text" class="form-control" id="description" name="sets[description]" maxlength="250" value="{{ getInput('sets.description', $settings['description']) }}" required>
        {!! textError('sets[description]') !!}
    </div>

    <div class="form-group{{ hasError('sets[nocheck]') }}">
        <label for="nocheck">Не сканируемые расширения (через запятую):</label>
        <input type="text" class="form-control" id="nocheck" name="sets[nocheck]" maxlength="100" value="{{ getInput('sets.nocheck', $settings['nocheck']) }}" required>
        {!! textError('sets[nocheck]') !!}
    </div>

    <div class="form-group{{ hasError('sets[moneyname]') }}">
        <label for="moneyname">Название денег:</label>
        <input type="text" class="form-control" id="moneyname" name="sets[moneyname]" maxlength="100" value="{{ getInput('sets.moneyname', $settings['moneyname']) }}" required>
        {!! textError('sets[moneyname]') !!}
    </div>

    <div class="form-group{{ hasError('sets[scorename]') }}">
        <label for="scorename">Название баллов:</label>
        <input type="text" class="form-control" id="scorename" name="sets[scorename]" maxlength="100" value="{{ getInput('sets.scorename', $settings['scorename']) }}" required>
        {!! textError('sets[scorename]') !!}
    </div>

    <div class="form-group{{ hasError('sets[statusname]') }}">
        <label for="statusname">Статусы пользователей:</label>
        <input type="text" class="form-control" id="statusname" name="sets[statusname]" maxlength="100" value="{{ getInput('sets.statusname', $settings['statusname']) }}" required>
        {!! textError('sets[statusname]') !!}
    </div>

    <div class="form-group{{ hasError('sets[statusdef]') }}">
        <label for="statusdef">Статус по умолчанию:</label>
        <input type="text" class="form-control" id="statusdef" name="sets[statusdef]" maxlength="20" value="{{ getInput('sets.statusdef', $settings['statusdef']) }}" required>
        {!! textError('sets[statusdef]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[addbansend]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[addbansend]" id="addbansend"{{ getInput('sets.addbansend', $settings['addbansend']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="addbansend">Объяснение из бана</label>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
