@section('header')
    <h1>Стикеры</h1>
@stop

<form action="/admin/settings?act=sticker" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[stickermaxsize]') }}">
        <label for="stickermaxsize">Максимальный вес стикера (kb):</label>
        <input type="number" class="form-control" id="stickermaxsize" name="sets[stickermaxsize]" maxlength="3" value="{{ getInput('sets.stickermaxsize', round($settings['stickermaxsize'] / 1024)) }}" required>
        {!! textError('sets[stickermaxsize]') !!}

        <input type="hidden" value="1024" name="mods[stickermaxsize]">
    </div>

    <div class="form-group{{ hasError('sets[stickermaxweight]') }}">
        <label for="stickermaxweight">Максимальный размер стикера (px):</label>
        <input type="number" class="form-control" id="stickermaxweight" name="sets[stickermaxweight]" maxlength="3" value="{{ getInput('sets.stickermaxweight', $settings['stickermaxweight']) }}" required>
        {!! textError('sets[stickermaxweight]') !!}
    </div>

    <div class="form-group{{ hasError('sets[stickerminweight]') }}">
        <label for="stickerminweight">Минимальный размер стикера (px):</label>
        <input type="number" class="form-control" id="stickerminweight" name="sets[stickerminweight]" maxlength="3" value="{{ getInput('sets.stickerminweight', $settings['stickerminweight']) }}" required>
        {!! textError('sets[stickerminweight]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
