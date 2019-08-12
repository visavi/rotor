@section('header')
    <h1>{{ trans('settings.photos') }}</h1>
@stop

<form action="/admin/settings?act=photos" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[fotolist]') }}">
        <label for="fotolist">{{ trans('settings.photos_per_page') }}:</label>
        <input type="number" class="form-control" id="fotolist" name="sets[fotolist]" maxlength="2" value="{{ getInput('sets.fotolist', $settings['fotolist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[fotolist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[postgallery]') }}">
        <label for="postgallery">{{ trans('settings.photos_comments') }}:</label>
        <input type="number" class="form-control" id="postgallery" name="sets[postgallery]" maxlength="3" value="{{ getInput('sets.postgallery', $settings['postgallery']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postgallery]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[photogroup]') }}">
        <label for="photogroup">{{ trans('settings.photos_groups') }}:</label>
        <input type="number" class="form-control" id="photogroup" name="sets[photogroup]" maxlength="2" value="{{ getInput('sets.photogroup', $settings['photogroup']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[photogroup]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
