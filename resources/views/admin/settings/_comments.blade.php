@section('header')
    <h1>{{ __('settings.comments') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[comment_length]') }}">
        <label for="comment_length" class="form-label">{{ __('settings.comments_symbols') }}:</label>
        <input type="number" class="form-control" id="comment_length" name="sets[comment_length]" maxlength="5" value="{{ getInput('sets.comment_length', $settings['comment_length']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[comment_length]') }}</div>
    </div>

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

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
