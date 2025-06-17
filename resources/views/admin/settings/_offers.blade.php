@section('header')
    <h1>{{ __('settings.offers') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[postoffers]') }}">
        <label for="postoffers" class="form-label">{{ __('settings.offers_per_page') }}:</label>
        <input type="number" class="form-control" id="postoffers" name="sets[postoffers]" maxlength="2" value="{{ getInput('sets.postoffers', $settings['postoffers']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[postoffers]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[addofferspoint]') }}">
        <label for="addofferspoint" class="form-label">{{ __('settings.offers_points') }}:</label>
        <input type="number" class="form-control" id="addofferspoint" name="sets[addofferspoint]" maxlength="4" value="{{ getInput('sets.addofferspoint', $settings['addofferspoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[addofferspoint]') }}</div>
    </div>

    <div class="mb-3">
        <label for="offer_title_min" class="form-label">{{ __('settings.offer_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[offer_title_min]') }}" id="offer_title_min" name="sets[offer_title_min]" value="{{ old('sets.offer_title_min', $settings['offer_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[offer_title_max]') }}" name="sets[offer_title_max]" value="{{ old('sets.offer_title_max', $settings['offer_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[offer_title_min]') }}</div>
            <div>{{ textError('sets[offer_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="offer_text_min" class="form-label">{{ __('settings.offer_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[offer_text_min]') }}" id="offer_text_min" name="sets[offer_text_min]" value="{{ old('sets.offer_text_min', $settings['offer_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[offer_text_max]') }}" name="sets[offer_text_max]" value="{{ old('sets.offer_text_max', $settings['offer_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[offer_text_min]') }}</div>
            <div>{{ textError('sets[offer_text_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="offer_reply_min" class="form-label">{{ __('settings.offer_reply_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[offer_reply_min]') }}" id="offer_reply_min" name="sets[offer_reply_min]" value="{{ old('sets.offer_reply_min', $settings['offer_reply_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[offer_reply_max]') }}" name="sets[offer_reply_max]" value="{{ old('sets.offer_reply_max', $settings['offer_reply_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[offer_reply_min]') }}</div>
            <div>{{ textError('sets[offer_reply_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
