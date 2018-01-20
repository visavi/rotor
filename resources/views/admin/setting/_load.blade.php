<h3>Загруз-центр</h3>

<form action="/admin/setting?act=load" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[downlist]') }}">
        <label for="downlist">Файлов в загрузках:</label>
        <input type="number" class="form-control" id="downlist" name="sets[downlist]" maxlength="2" value="{{ getInput('sets[downlist]', $settings['downlist']) }}" required>
        {!! textError('sets[downlist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[downcomm]') }}">
        <label for="downcomm">Комментариев в загрузках:</label>
        <input type="number" class="form-control" id="downcomm" name="sets[downcomm]" maxlength="2" value="{{ getInput('sets[downcomm]', $settings['downcomm']) }}" required>
        {!! textError('sets[downcomm]') !!}
    </div>

    <div class="form-group{{ hasError('sets[ziplist]') }}">
        <label for="ziplist">Просмотр архивов на стр.:</label>
        <input type="number" class="form-control" id="ziplist" name="sets[ziplist]" maxlength="2" value="{{ getInput('sets[ziplist]', $settings['ziplist']) }}" required>
        {!! textError('sets[ziplist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[fileupload]') }}">
        <label for="fileupload">Максимальный вес файла (Mb):</label>
        <input type="number" class="form-control" id="fileupload" name="sets[fileupload]" maxlength="3" value="{{ getInput('sets[fileupload]', round($settings['fileupload'] / 1048576)) }}" required>
        {!! textError('sets[fileupload]') !!}

        <input type="hidden" value="1048576" name="mods[fileupload]">
    </div>

    <div class="form-group{{ hasError('sets[screenupload]') }}">
        <label for="screenupload">Максимальный вес скриншота (Mb):</label>
        <input type="number" class="form-control" id="screenupload" name="sets[screenupload]" maxlength="3" value="{{ getInput('sets[screenupload]', round($settings['screenupload'] / 1048576)) }}" required>
        {!! textError('sets[screenupload]') !!}

        <input type="hidden" value="1048576" name="mods[screenupload]">
        <span class="text-muted font-italic">Ограничение сервера: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[screenupsize]') }}">
        <label for="screenupsize">Максимальный размер скриншота (px):</label>
        <input type="number" class="form-control" id="screenupsize" name="sets[screenupsize]" maxlength="4" value="{{ getInput('sets[screenupsize]', $settings['screenupsize']) }}" required>
        {!! textError('sets[screenupsize]') !!}
    </div>

    <div class="form-group{{ hasError('sets[allowextload]') }}">
        <label for="allowextload">Допустимые расширения файлов:</label>
        <textarea class="form-control" id="allowextload" name="sets[allowextload]" required>{{ getInput('sets[allowextload]', $settings['allowextload']) }}</textarea>
        {!! textError('sets[allowextload]') !!}
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[downupload]">
            <input name="sets[downupload]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets[downupload]', $settings['downupload']) ? ' checked' : '' }}>
            Разрешать загружать файлы пользователям
        </label>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
