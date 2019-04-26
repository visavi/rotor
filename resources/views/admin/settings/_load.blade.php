@section('header')
    <h1>Загруз-центр</h1>
@stop

<form action="/admin/settings?act=load" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[maxfiles]') }}">
        <label for="maxfiles">Одновременно загружаемое кол. файлов:</label>
        <input type="number" class="form-control" id="maxfiles" name="sets[maxfiles]" maxlength="2" value="{{ getInput('sets.maxfiles', $settings['maxfiles']) }}" required>
        {!! textError('sets[maxfiles]') !!}
    </div>


    <div class="form-group{{ hasError('sets[downlist]') }}">
        <label for="downlist">Файлов в загрузках:</label>
        <input type="number" class="form-control" id="downlist" name="sets[downlist]" maxlength="2" value="{{ getInput('sets.downlist', $settings['downlist']) }}" required>
        {!! textError('sets[downlist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[downcomm]') }}">
        <label for="downcomm">Комментариев в загрузках:</label>
        <input type="number" class="form-control" id="downcomm" name="sets[downcomm]" maxlength="2" value="{{ getInput('sets.downcomm', $settings['downcomm']) }}" required>
        {!! textError('sets[downcomm]') !!}
    </div>

    <div class="form-group{{ hasError('sets[ziplist]') }}">
        <label for="ziplist">Просмотр архивов на стр.:</label>
        <input type="number" class="form-control" id="ziplist" name="sets[ziplist]" maxlength="2" value="{{ getInput('sets.ziplist', $settings['ziplist']) }}" required>
        {!! textError('sets[ziplist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[fileupload]') }}">
        <label for="fileupload">Максимальный вес файла (Mb):</label>
        <input type="number" class="form-control" id="fileupload" name="sets[fileupload]" maxlength="3" value="{{ getInput('sets.fileupload', round($settings['fileupload'] / 1048576)) }}" required>
        {!! textError('sets[fileupload]') !!}

        <input type="hidden" value="1048576" name="mods[fileupload]">
        <span class="text-muted font-italic">Ограничение сервера: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[allowextload]') }}">
        <label for="allowextload">Допустимые расширения файлов:</label>
        <textarea class="form-control" id="allowextload" name="sets[allowextload]" required>{{ getInput('sets.allowextload', $settings['allowextload']) }}</textarea>
        {!! textError('sets[allowextload]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[downupload]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[downupload]" id="downupload"{{ getInput('sets.downupload', $settings['downupload']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="downupload">Разрешать загружать файлы пользователям</label>
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
