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

    <div class="mb-3">
        <label for="news_title_min" class="form-label">{{ __('settings.news_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[news_title_min]') }}" id="news_title_min" name="sets[news_title_min]" value="{{ old('sets.news_title_min', $settings['news_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[news_title_max]') }}" name="sets[news_title_max]" value="{{ old('sets.news_title_max', $settings['news_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[news_title_min]') }}</div>
            <div>{{ textError('sets[news_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="news_text_min" class="form-label">{{ __('settings.news_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[news_text_min]') }}" id="news_text_min" name="sets[news_text_min]" value="{{ old('sets.news_text_min', $settings['news_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[news_text_max]') }}" name="sets[news_text_max]" value="{{ old('sets.news_text_max', $settings['news_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[news_title_max]') }}</div>
            <div>{{ textError('sets[news_text_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
