@section('header')
    <h1>{{ __('settings.feeds') }}</h1>
@stop

<form method="post">
    @csrf

    <?php $views = [
        'classic' => __('settings.homepage_view_classic'),
        'feed' => __('settings.homepage_view_feed'),
    ]; ?>
    <?php $inputView = getInput('language', $settings['homepage_view']); ?>

    <div class="mb-3{{ hasError('sets[homepage_view]') }}">
        <label for="language" class="form-label">{{ __('settings.homepage_view') }}:</label>
        <select class="form-select" id="language" name="sets[homepage_view]">

            @foreach ($views as $view => $name)
                <?php $selected = ($view === $inputView) ? ' selected' : ''; ?>
                <option value="{{ $view }}"{{ $selected }}>{{ $name }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('sets[homepage_view]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_per_page]') }}">
        <label for="feed_per_page" class="form-label">{{ __('settings.feed_per_page') }}:</label>
        <input type="number" class="form-control" id="feed_per_page" name="sets[feed_per_page]" maxlength="3" value="{{ getInput('sets.feed_per_page', $settings['feed_per_page']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_per_page]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_last_record]') }}">
        <label for="feed_last_record" class="form-label">{{ __('settings.feed_last_record') }}:</label>
        <input type="number" class="form-control" id="feed_last_record" name="sets[feed_last_record]" maxlength="3" value="{{ getInput('sets.feed_last_record', $settings['feed_last_record']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_last_record]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_total]') }}">
        <label for="feed_total" class="form-label">{{ __('settings.feed_total') }}:</label>
        <input type="number" class="form-control" id="feed_total" name="sets[feed_total]" maxlength="4" value="{{ getInput('sets.feed_total', $settings['feed_total']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_total]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_topics_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_topics_show]" id="feed_topics_show"{{ getInput('sets.feed_topics_show', $settings['feed_topics_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_topics_show">{{ __('settings.feed_topics_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_news_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_news_show]" id="feed_news_show"{{ getInput('sets.feed_news_show', $settings['feed_news_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_news_show">{{ __('settings.feed_news_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_photos_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_photos_show]" id="feed_photos_show"{{ getInput('sets.feed_photos_show', $settings['feed_photos_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_photos_show">{{ __('settings.feed_photos_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_articles_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_articles_show]" id="feed_articles_show"{{ getInput('sets.feed_articles_show', $settings['feed_articles_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_articles_show">{{ __('settings.feed_articles_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_downs_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_downs_show]" id="feed_downs_show"{{ getInput('sets.feed_downs_show', $settings['feed_downs_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_downs_show">{{ __('settings.feed_downs_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_items_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_items_show]" id="feed_items_show"{{ getInput('sets.feed_items_show', $settings['feed_items_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_items_show">{{ __('settings.feed_items_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_offers_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_offers_show]" id="feed_offers_show"{{ getInput('sets.feed_offers_show', $settings['feed_offers_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_offers_show">{{ __('settings.feed_offers_show') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[feed_comments_show]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[feed_comments_show]" id="feed_comments_show"{{ getInput('sets.feed_comments_show', $settings['feed_comments_show']) ? ' checked' : '' }}>
        <label class="form-check-label" for="feed_comments_show">{{ __('settings.feed_comments_show') }}</label>
    </div>

    <div class="mb-3{{ hasError('sets[feed_topics_rating]') }}">
        <label for="feed_topics_rating" class="form-label">{{ __('settings.feed_topics_rating') }}:</label>
        <input type="number" class="form-control" id="feed_topics_rating" name="sets[feed_topics_rating]" maxlength="2" value="{{ getInput('sets.feed_topics_rating', $settings['feed_topics_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_topics_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_news_rating]') }}">
        <label for="feed_news_rating" class="form-label">{{ __('settings.feed_news_rating') }}:</label>
        <input type="number" class="form-control" id="feed_news_rating" name="sets[feed_news_rating]" maxlength="2" value="{{ getInput('sets.feed_news_rating', $settings['feed_news_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_news_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_photos_rating]') }}">
        <label for="feed_photos_rating" class="form-label">{{ __('settings.feed_photos_rating') }}:</label>
        <input type="number" class="form-control" id="feed_photos_rating" name="sets[feed_photos_rating]" maxlength="2" value="{{ getInput('sets.feed_photos_rating', $settings['feed_photos_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_photos_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_articles_rating]') }}">
        <label for="feed_articles_rating" class="form-label">{{ __('settings.feed_articles_rating') }}:</label>
        <input type="number" class="form-control" id="feed_articles_rating" name="sets[feed_articles_rating]" maxlength="2" value="{{ getInput('sets.feed_articles_rating', $settings['feed_articles_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_articles_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_downs_rating]') }}">
        <label for="feed_downs_rating" class="form-label">{{ __('settings.feed_downs_rating') }}:</label>
        <input type="number" class="form-control" id="feed_downs_rating" name="sets[feed_downs_rating]" maxlength="2" value="{{ getInput('sets.feed_downs_rating', $settings['feed_downs_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_downs_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_offers_rating]') }}">
        <label for="feed_offers_rating" class="form-label">{{ __('settings.feed_offers_rating') }}:</label>
        <input type="number" class="form-control" id="feed_offers_rating" name="sets[feed_offers_rating]" maxlength="2" value="{{ getInput('sets.feed_offers_rating', $settings['feed_offers_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_offers_rating]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[feed_comments_rating]') }}">
        <label for="feed_comments_rating" class="form-label">{{ __('settings.feed_comments_rating') }}:</label>
        <input type="number" class="form-control" id="feed_comments_rating" name="sets[feed_comments_rating]" maxlength="2" value="{{ getInput('sets.feed_comments_rating', $settings['feed_comments_rating']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[feed_comments_rating]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
