@section('header')
    <h1>{{ trans('settings.stickers') }}</h1>
@stop

<form action="/admin/settings?act=stickers" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[stickermaxsize]') }}">
        <label for="stickermaxsize">Максимальный вес стикера (kb):</label>
        <input type="number" class="form-control" id="stickermaxsize" name="sets[stickermaxsize]" maxlength="3" value="{{ getInput('sets.stickermaxsize', round($settings['stickermaxsize'] / 1024)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickermaxsize]') }}</div>

        <input type="hidden" value="1024" name="mods[stickermaxsize]">
    </div>

    <div class="form-group{{ hasError('sets[stickermaxweight]') }}">
        <label for="stickermaxweight">Максимальный размер стикера (px):</label>
        <input type="number" class="form-control" id="stickermaxweight" name="sets[stickermaxweight]" maxlength="3" value="{{ getInput('sets.stickermaxweight', $settings['stickermaxweight']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickermaxweight]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[stickerminweight]') }}">
        <label for="stickerminweight">Минимальный размер стикера (px):</label>
        <input type="number" class="form-control" id="stickerminweight" name="sets[stickerminweight]" maxlength="3" value="{{ getInput('sets.stickerminweight', $settings['stickerminweight']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickerminweight]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[stickerlist]') }}">
        <label for="stickerlist">Стикеров на стр.:</label>
        <input type="number" class="form-control" id="stickerlist" name="sets[stickerlist]" maxlength="2" value="{{ getInput('sets.stickerlist', $settings['stickerlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickerlist]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
