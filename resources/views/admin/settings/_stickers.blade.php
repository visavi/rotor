@section('header')
    <h1>{{ __('settings.stickers') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[stickermaxsize]') }}">
        <label for="stickermaxsize" class="form-label">{{ __('settings.stickers_size') }} (kb):</label>
        <input type="number" class="form-control" id="stickermaxsize" name="sets[stickermaxsize]" maxlength="3" value="{{ getInput('sets.stickermaxsize', round($settings['stickermaxsize'] / 1024)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickermaxsize]') }}</div>

        <input type="hidden" value="1024" name="mods[stickermaxsize]">
    </div>

    <div class="mb-3{{ hasError('sets[stickerminweight]') }}">
        <label for="stickerminweight" class="form-label">{{ __('settings.stickers_min_weight') }} (px):</label>
        <input type="number" class="form-control" id="stickerminweight" name="sets[stickerminweight]" maxlength="3" value="{{ getInput('sets.stickerminweight', $settings['stickerminweight']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickerminweight]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[stickermaxweight]') }}">
        <label for="stickermaxweight" class="form-label">{{ __('settings.stickers_max_weight') }} (px):</label>
        <input type="number" class="form-control" id="stickermaxweight" name="sets[stickermaxweight]" maxlength="3" value="{{ getInput('sets.stickermaxweight', $settings['stickermaxweight']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickermaxweight]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[stickerlist]') }}">
        <label for="stickerlist" class="form-label">{{ __('settings.stickers_per_page') }}:</label>
        <input type="number" class="form-control" id="stickerlist" name="sets[stickerlist]" maxlength="2" value="{{ getInput('sets.stickerlist', $settings['stickerlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[stickerlist]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
