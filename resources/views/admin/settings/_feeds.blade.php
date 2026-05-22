@section('header')
    <h1>{{ __('settings.feeds') }}</h1>
@stop

<form method="post">
    @csrf

    <div class="mb-3{{ hasError('sets[feed_per_page]') }}">
        <label for="feed_per_page" class="form-label">{{ __('settings.feed_per_page') }}:</label>
        <input type="number" class="form-control" id="feed_per_page" name="sets[feed_per_page]" maxlength="3" value="{{ getInput('sets.feed_per_page', $settings['feed_per_page']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_per_page]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_cache_time]') }}">
        <label for="feed_cache_time" class="form-label">{{ __('settings.feed_cache_time') }}:</label>
        <input type="number" class="form-control" id="feed_cache_time" name="sets[feed_cache_time]" maxlength="5" value="{{ getInput('sets.feed_cache_time', $settings['feed_cache_time']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_cache_time]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_comments_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_comments_show]" id="feed_comments_show"{{ getInput('sets.feed_comments_show', $settings['feed_comments_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_comments_show">{{ __('settings.feed_comments_show') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[feed_comments_rating]') }}">
        <label for="feed_comments_rating" class="form-label">{{ __('settings.feed_comments_rating') }}:</label>
        <input type="number" class="form-control" id="feed_comments_rating" name="sets[feed_comments_rating]" maxlength="2" value="{{ getInput('sets.feed_comments_rating', $settings['feed_comments_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_comments_rating]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
