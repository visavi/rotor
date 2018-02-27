<h3>Форум / Галерея</h3>

<form action="/admin/setting?act=forum" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[forumtem]') }}">
        <label for="forumtem">Тем в форуме на стр.:</label>
        <input type="number" class="form-control" id="forumtem" name="sets[forumtem]" maxlength="2" value="{{ getInput('sets.forumtem', $settings['forumtem']) }}" required>
        {!! textError('sets[forumtem]') !!}
    </div>

    <div class="form-group{{ hasError('sets[forumpost]') }}">
        <label for="forumpost">Сообщений в форуме на стр.:</label>
        <input type="number" class="form-control" id="forumpost" name="sets[forumpost]" maxlength="2" value="{{ getInput('sets.forumpost', $settings['forumpost']) }}" required>
        {!! textError('sets[forumpost]') !!}
    </div>

    <div class="form-group{{ hasError('sets[forumtextlength]') }}">
        <label for="forumtextlength">Символов в сообщении форума:</label>
        <input type="number" class="form-control" id="forumtextlength" name="sets[forumtextlength]" maxlength="5" value="{{ getInput('sets.forumtextlength', $settings['forumtextlength']) }}" required>
        {!! textError('sets[forumtextlength]') !!}
    </div>

    <div class="form-group{{ hasError('sets[forumloadsize]') }}">
        <label for="forumloadsize">Максимальный вес файла (Mb):</label>
        <input type="number" class="form-control" id="forumloadsize" name="sets[forumloadsize]" maxlength="2" value="{{ getInput('sets.forumloadsize', round($settings['forumloadsize'] / 1048576)) }}" required>
        {!! textError('sets[forumloadsize]') !!}

        <input type="hidden" value="1048576" name="mods[forumloadsize]">
        <span class="text-muted font-italic">Ограничение сервера: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[forumextload]') }}">
        <label for="forumextload">Допустимые расширения файлов:</label>
        <textarea class="form-control" id="forumextload" name="sets[forumextload]" required>{{ getInput('sets.forumextload', $settings['forumextload']) }}</textarea>
        {!! textError('sets[forumextload]') !!}
    </div>

    <div class="form-group{{ hasError('sets[forumloadpoints]') }}">
        <label for="forumloadpoints">Актива для загрузки файлов:</label>
        <input type="number" class="form-control" id="forumloadpoints" name="sets[forumloadpoints]" maxlength="4" value="{{ getInput('sets.forumloadpoints', $settings['forumloadpoints']) }}" required>
        {!! textError('sets[forumloadpoints]') !!}
    </div>

    <div class="form-group{{ hasError('sets[fotolist]') }}">
        <label for="fotolist">Kол-во фото на стр.</label>
        <input type="number" class="form-control" id="fotolist" name="sets[fotolist]" maxlength="2" value="{{ getInput('sets.fotolist', $settings['fotolist']) }}" required>
        {!! textError('sets[fotolist]') !!}
    </div>

    <div class="form-group{{ hasError('sets[postgallery]') }}">
        <label for="postgallery">Комментариев на страницу в галерее</label>
        <input type="number" class="form-control" id="postgallery" name="sets[postgallery]" maxlength="3" value="{{ getInput('sets.postgallery', $settings['postgallery']) }}" required>
        {!! textError('sets[postgallery]') !!}
    </div>

    <div class="form-group{{ hasError('sets[photogroup]') }}">
        <label for="photogroup">Групп на страницу в галерее:</label>
        <input type="number" class="form-control" id="photogroup" name="sets[photogroup]" maxlength="2" value="{{ getInput('sets.photogroup', $settings['photogroup']) }}" required>
        {!! textError('sets[photogroup]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
