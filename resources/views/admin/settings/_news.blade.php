@section('header')
    <h1>{{ __('settings.news') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[postnews]') }}">
        <label for="postnews" class="form-label">{{ __('settings.news_per_page') }}:</label>
        <input type="number" class="form-control" id="postnews" name="sets[postnews]" maxlength="2" value="{{ getInput('sets.postnews', $settings['postnews']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postnews]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
