@section('header')
    <h1>{{ __('settings.photos') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="form-group{{ hasError('sets[fotolist]') }}">
        <label for="fotolist">{{ __('settings.photos_per_page') }}:</label>
        <input type="number" class="form-control" id="fotolist" name="sets[fotolist]" maxlength="2" value="{{ getInput('sets.fotolist', $settings['fotolist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[fotolist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[photogroup]') }}">
        <label for="photogroup">{{ __('settings.photos_groups') }}:</label>
        <input type="number" class="form-control" id="photogroup" name="sets[photogroup]" maxlength="2" value="{{ getInput('sets.photogroup', $settings['photogroup']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[photogroup]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
