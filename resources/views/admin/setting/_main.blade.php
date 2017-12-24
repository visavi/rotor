<form action="/admin/setting" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[title]') }}">
        <label for="inputTitle">Заголовок всех страниц:</label>
        <input type="text" class="form-control" id="inputTitle" name="sets[title]" maxlength="100" value="{{ getInput('sets[title]', $settings['title']) }}" required2>
        {!! textError('sets[title]') !!}
    </div>

    <div class="form-group{{ hasError('sets[logos]') }}">
        <label for="inputLogos">Подпись вверху:</label>
        <input type="text" class="form-control" id="inputLogos" name="sets[logos]" maxlength="100" value="{{ getInput('sets[logos]', $settings['logos']) }}" required>
        {!! textError('sets[logos]') !!}
    </div>

    <div class="form-group{{ hasError('sets[copy]') }}">
        <label for="inputCopy">Подпись внизу:</label>
        <input type="text" class="form-control" id="inputCopy" name="sets[copy]" maxlength="100" value="{{ getInput('sets[copy]', $settings['copy']) }}" required>
        {!! textError('sets[copy]') !!}
    </div>

    <div class="form-group{{ hasError('sets[logotip]') }}">
        <label for="inputLogotip">Адрес логотипа:</label>
        <input type="text" class="form-control" id="inputLogotip" name="sets[logotip]" maxlength="100" value="{{ getInput('sets[logotip]', $settings['logotip']) }}" required>
        {!! textError('sets[logotip]') !!}
    </div>

    <div class="form-group{{ hasError('sets[floodstime]') }}">
        <label for="inputFloodstime">Время антифлуда (сек):</label>
        <input type="text" class="form-control" id="inputFloodstime" name="sets[floodstime]" maxlength="3" value="{{ getInput('sets[floodstime]', $settings['floodstime']) }}" required>
        {!! textError('sets[floodstime]') !!}
    </div>

    <div class="form-group{{ hasError('sets[doslimit]') }}">
        <label for="inputDoslimit">Лимит запросов с IP (0 - Выкл):</label>
        <input type="text" class="form-control" id="inputDoslimit" name="sets[doslimit]" maxlength="3" value="{{ getInput('sets[doslimit]', $settings['doslimit']) }}" required>
        {!! textError('sets[doslimit]') !!}
    </div>

    <div class="form-group{{ hasError('sets[timezone]') }}">
        <label for="inputTimezone">Временная зона:</label>
        <input type="text" class="form-control" id="inputTimezone" name="sets[timezone]" maxlength="50" value="{{ getInput('sets[timezone]', $settings['timezone']) }}" required>
        {!! textError('sets[timezone]') !!}
    </div>

    <?php $themes = glob(HOME."/themes/*", GLOB_ONLYDIR); ?>

    <?php $inputThemes = getInput('sets[themes]', $settings['themes']); ?>

    <div class="form-group{{ hasError('sets[themes]') }}">
        <label for="inputThemes">Wap-тема:</label>
        <select class="form-control" id="inputThemes" name="sets[themes]">

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes == basename($theme)) ? ' selected' : ''; ?>
                <option value="{{ basename($theme) }}"{{ $selected }}>{{ basename($theme) }}</option>
            @endforeach

        </select>
        {!! textError('sets[themes]') !!}
    </div>

    <?php $inputThemes = getInput('sets[webthemes]', $settings['webthemes']); ?>

    <div class="form-group{{ hasError('sets[webthemes]') }}">
        <label for="inputWebthemes">Web-тема:</label>
        <select class="form-control" id="inputWebthemes" name="sets[webthemes]">
            <option value="0">Выключить</option>

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes == basename($theme)) ? ' selected' : ''; ?>
                <option value="{{ basename($theme) }}"{{ $selected }}>{{ basename($theme) }}</option>
            @endforeach

        </select>
        {!! textError('sets[webthemes]') !!}
    </div>

    <?php $inputSite = getInput('sets[closedsite]', $settings['closedsite']); ?>
    <?php $statsite = ['Сайт открыт', 'Закрыто для гостей', 'Закрыто для всех']; ?>

    <div class="form-group{{ hasError('sets[closedsite]') }}">
        <label for="inputClosedsites">Доступ к сайту:</label>
        <select class="form-control" id="inputClosedsites" name="sets[closedsite]">

            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key == $settings['closedsite']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach

        </select>
        {!! textError('sets[closedsite]') !!}
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[openreg]">
            <input name="sets[openreg]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets[openreg]', $settings['openreg']) ? ' checked' : '' }}>
            Разрешить регистрацию
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[regkeys]">
            <input name="sets[regkeys]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets[regkeys]', $settings['regkeys']) ? ' checked' : '' }}>
            Подтверждение регистрации
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[invite]">
            <input name="sets[invite]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets[invite]', $settings['invite']) ? ' checked' : '' }}>
            Регистрация по приглашениям
        </label>
    </div>
    <p class="text-muted font-italic">
        Для регистрация необходимо ввести специальный пригласительный ключ
    </p>

    <button class="btn btn-primary">Сохранить</button>
</form>


