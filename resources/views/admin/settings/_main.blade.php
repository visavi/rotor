@section('header')
    <h1>Основные настройки</h1>
@stop

<form action="/admin/settings" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[title]') }}">
        <label for="title">Заголовок всех страниц:</label>
        <input type="text" class="form-control" id="title" name="sets[title]" maxlength="100" value="{{ getInput('sets.title', $settings['title']) }}" required>
        {!! textError('sets[title]') !!}
    </div>

    <div class="form-group{{ hasError('sets[logos]') }}">
        <label for="logos">Подпись вверху:</label>
        <input type="text" class="form-control" id="logos" name="sets[logos]" maxlength="100" value="{{ getInput('sets.logos', $settings['logos']) }}" required>
        {!! textError('sets[logos]') !!}
    </div>

    <div class="form-group{{ hasError('sets[copy]') }}">
        <label for="copy">Подпись внизу:</label>
        <input type="text" class="form-control" id="copy" name="sets[copy]" maxlength="100" value="{{ getInput('sets.copy', $settings['copy']) }}" required>
        {!! textError('sets[copy]') !!}
    </div>

    <div class="form-group{{ hasError('sets[logotip]') }}">
        <label for="logotip">Адрес логотипа:</label>
        <input type="text" class="form-control" id="logotip" name="sets[logotip]" maxlength="100" value="{{ getInput('sets.logotip', $settings['logotip']) }}" required>
        {!! textError('sets[logotip]') !!}
    </div>

    <div class="form-group{{ hasError('sets[floodstime]') }}">
        <label for="floodstime">Время антифлуда (сек):</label>
        <input type="number" class="form-control" id="floodstime" name="sets[floodstime]" maxlength="3" value="{{ getInput('sets.floodstime', $settings['floodstime']) }}" required>
        {!! textError('sets[floodstime]') !!}
    </div>

    <div class="form-group{{ hasError('sets[doslimit]') }}">
        <label for="doslimit">Лимит запросов с IP (0 - Выкл):</label>
        <input type="number" class="form-control" id="doslimit" name="sets[doslimit]" maxlength="3" value="{{ getInput('sets.doslimit', $settings['doslimit']) }}" required>
        {!! textError('sets[doslimit]') !!}
    </div>

    <div class="form-group{{ hasError('sets[timezone]') }}">
        <label for="timezone">Временная зона:</label>
        <input type="text" class="form-control" id="timezone" name="sets[timezone]" maxlength="50" value="{{ getInput('sets.timezone', $settings['timezone']) }}" required>
        {!! textError('sets[timezone]') !!}
    </div>

    <div class="form-group{{ hasError('sets[currency]') }}">
        <label for="currency">Валюта сайта:</label>
        <input type="text" class="form-control" id="currency" name="sets[currency]" maxlength="10" value="{{ getInput('sets.currency', $settings['currency']) }}" required>
        {!! textError('sets[currency]') !!}
    </div>

    <?php $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR)); ?>
    <?php $inputLang = getInput('language', $settings['language']); ?>

    <div class="form-group{{ hasError('sets[language]') }}">
        <label for="language">Язык:</label>
        <select class="form-control" id="language" name="sets[language]">

            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        {!! textError('sets[language]') !!}
    </div>

    <?php $inputLangFallback = getInput('language_fallback', $settings['language_fallback']); ?>

    <div class="form-group{{ hasError('sets[language_fallback]') }}">
        <label for="language_fallback">Резервный язык:</label>
        <select class="form-control" id="language_fallback" name="sets[language_fallback]">

            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLangFallback) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        {!! textError('sets[language_fallback]') !!}
    </div>

    <p class="text-muted font-italic">
       Резервный язык используется когда нет перевода основного языка
    </p>

    <?php $themes = array_map('basename', glob(HOME . '/themes/*', GLOB_ONLYDIR)); ?>
    <?php $inputThemes = getInput('sets.themes', $settings['themes']); ?>

    <div class="form-group{{ hasError('sets[themes]') }}">
        <label for="themes">Wap-тема:</label>
        <select class="form-control" id="themes" name="sets[themes]">

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes === $theme) ? ' selected' : ''; ?>
                <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
            @endforeach

        </select>
        {!! textError('sets[themes]') !!}
    </div>

    <?php $inputThemes = getInput('sets.webthemes', $settings['webthemes']); ?>

    <div class="form-group{{ hasError('sets[webthemes]') }}">
        <label for="webthemes">Web-тема:</label>
        <select class="form-control" id="webthemes" name="sets[webthemes]">
            <option value="0">Выключить</option>

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes === $theme) ? ' selected' : ''; ?>
                <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
            @endforeach

        </select>
        {!! textError('sets[webthemes]') !!}
    </div>

    <?php $inputSite = getInput('sets.closedsite', $settings['closedsite']); ?>
    <?php $statsite = ['Сайт открыт', 'Закрыто для гостей', 'Закрыто для всех']; ?>

    <div class="form-group{{ hasError('sets[closedsite]') }}">
        <label for="closedsite">Доступ к сайту:</label>
        <select class="form-control" id="closedsite" name="sets[closedsite]">

            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key === (int) $settings['closedsite']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach

        </select>
        {!! textError('sets[closedsite]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[openreg]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[openreg]" id="openreg"{{ getInput('sets.openreg', $settings['openreg']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="openreg">Разрешить регистрацию</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[regkeys]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[regkeys]" id="regkeys"{{ getInput('sets.regkeys', $settings['regkeys']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="regkeys">Подтверждение регистрации</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[invite]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[invite]" id="invite"{{ getInput('sets.invite', $settings['invite']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="invite">Регистрация по приглашениям</label>
    </div>

    <p class="text-muted font-italic">
        Для регистрация необходимо ввести специальный пригласительный ключ
    </p>

    <button class="btn btn-primary">Сохранить</button>
</form>
