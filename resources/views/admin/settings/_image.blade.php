<h3>Загрузка изображений</h3>

<form action="/admin/settings?act=image" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[filesize]') }}">
        <label for="filesize">Максимальный вес фото (Mb):</label>
        <input type="number" class="form-control" id="filesize" name="sets[filesize]" maxlength="3" value="{{ getInput('sets.filesize', round($settings['filesize'] / 1048576)) }}" required>
        {!! textError('sets[filesize]') !!}

        <input type="hidden" value="1048576" name="mods[filesize]">
        <span class="text-muted font-italic">Ограничение сервера: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[screensize]') }}">
        <label for="screensize">Уменьшение фото при загрузке (px):</label>
        <input type="number" class="form-control" id="screensize" name="sets[screensize]" maxlength="4" value="{{ getInput('sets.screensize', $settings['screensize']) }}" required>
        {!! textError('sets[screensize]') !!}
    </div>

    <div class="form-group{{ hasError('sets[previewsize]') }}">
        <label for="previewsize">Размер превью (px):</label>
        <input type="number" class="form-control" id="previewsize" name="sets[previewsize]" maxlength="3" value="{{ getInput('sets.previewsize', $settings['previewsize']) }}" required>
        {!! textError('sets[previewsize]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[copyfoto]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[copyfoto]" id="copyfoto"{{ getInput('sets.copyfoto', $settings['copyfoto']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="copyfoto">Наложение копирайта</label>
    </div>

    <img src="/assets/img/images/watermark.png" alt="watermark" title="{{ siteUrl() }}/assets/img/images/watermark.png"><br>

    <p class="text-muted font-italic">
        Не устанавливайте слишком большие размеры веса и размера изображений, так как может не хватить процессорного времени для обработки<br>
        При изменении размера превью, необходимо вручную очистить кэш изображений
    </p>

    <button class="btn btn-primary">Сохранить</button>
</form>
