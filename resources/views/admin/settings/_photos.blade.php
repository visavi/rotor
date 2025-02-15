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

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
