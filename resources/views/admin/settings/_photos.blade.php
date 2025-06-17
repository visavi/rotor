@section('header')
    <h1>{{ __('settings.photos') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[fotolist]') }}">
        <label for="fotolist" class="form-label">{{ __('settings.photos_per_page') }}:</label>
        <input type="number" class="form-control" id="fotolist" name="sets[fotolist]" maxlength="2" value="{{ getInput('sets.fotolist', $settings['fotolist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[fotolist]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[photogroup]') }}">
        <label for="photogroup" class="form-label">{{ __('settings.photos_groups') }}:</label>
        <input type="number" class="form-control" id="photogroup" name="sets[photogroup]" maxlength="2" value="{{ getInput('sets.photogroup', $settings['photogroup']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[photogroup]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[photos_create]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[photos_create]" id="photos_create"{{ getInput('sets.photos_create', $settings['photos_create']) ? ' checked' : '' }}>
        <label class="form-check-label" for="photos_create">{{ __('settings.photos_create') }}</label>
    </div>

    <div class="mb-3">
        <label for="photo_title_min" class="form-label">{{ __('settings.photo_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[photo_title_min]') }}" id="photo_title_min" name="sets[photo_title_min]" value="{{ old('sets.photo_title_min', $settings['photo_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[photo_title_max]') }}" name="sets[photo_title_max]" value="{{ old('sets.photo_title_max', $settings['photo_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[photo_title_min]') }}</div>
            <div>{{ textError('sets[photo_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="photo_text_min" class="form-label">{{ __('settings.photo_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[photo_text_min]') }}" id="photo_text_min" name="sets[photo_text_min]" value="{{ old('sets.photo_text_min', $settings['photo_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[photo_text_max]') }}" name="sets[photo_text_max]" value="{{ old('sets.photo_text_max', $settings['photo_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[photo_text_min]') }}</div>
            <div>{{ textError('sets[photo_text_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
