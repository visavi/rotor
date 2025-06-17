@section('header')
    <h1>{{ __('settings.comments') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[comments_per_page]') }}">
        <label for="comments_per_page" class="form-label">{{ __('settings.comments_per_page') }}:</label>
        <input type="number" class="form-control" id="comments_per_page" name="sets[comments_per_page]" maxlength="3" value="{{ getInput('sets.comments_per_page', $settings['comments_per_page']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[comments_per_page]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[chatpost]') }}">
        <label for="chatpost" class="form-label">{{ __('settings.chat_per_page') }}:</label>
        <input type="number" class="form-control" id="chatpost" name="sets[chatpost]" maxlength="2" value="{{ getInput('sets.chatpost', $settings['chatpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[chatpost]') }}</div>
    </div>

    <div class="mb-3">
        <label for="comment_text_min" class="form-label">{{ __('settings.comments_symbols') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[comment_text_min]') }}" id="comment_text_min" name="sets[comment_text_min]" value="{{ old('sets.comment_text_min', $settings['comment_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[comment_text_max]') }}" name="sets[comment_text_max]" value="{{ old('sets.comment_text_max', $settings['comment_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[comment_text_min]') }}</div>
            <div>{{ textError('sets[comment_text_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
