@section('header')
    <h1>{{ __('settings.forums') }}</h1>
@stop

<form action="/admin/settings?act=forums" method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[forumtem]') }}">
        <label for="forumtem" class="form-label">{{ __('settings.topics_per_page') }}:</label>
        <input type="number" class="form-control" id="forumtem" name="sets[forumtem]" maxlength="2" value="{{ getInput('sets.forumtem', $settings['forumtem']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumtem]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[forumpost]') }}">
        <label for="forumpost" class="form-label">{{ __('settings.posts_per_page') }}:</label>
        <input type="number" class="form-control" id="forumpost" name="sets[forumpost]" maxlength="2" value="{{ getInput('sets.forumpost', $settings['forumpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumpost]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[forum_point]') }}">
        <label for="forum_point" class="form-label">{{ __('settings.forum_point') }}:</label>
        <input type="number" class="form-control" id="forum_point" name="sets[forum_point]" maxlength="2" value="{{ getInput('sets.forum_point', $settings['forum_point']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forum_point]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[forum_money]') }}">
        <label for="forum_money" class="form-label">{{ __('settings.forum_money') }}:</label>
        <input type="number" class="form-control" id="forum_money" name="sets[forum_money]" maxlength="2" value="{{ getInput('sets.forum_money', $settings['forum_money']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forum_money]') }}</div>
    </div>

    <div class="mb-3">
        <label for="forum_title_min" class="form-label">{{ __('settings.forum_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[forum_title_min]') }}" id="forum_title_min" name="sets[forum_title_min]" value="{{ old('sets.forum_title_min', $settings['forum_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[forum_title_max]') }}" name="sets[forum_title_max]" value="{{ old('sets.forum_title_max', $settings['forum_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[forum_title_min]') }}</div>
            <div>{{ textError('sets[forum_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="forum_note_min" class="form-label">{{ __('settings.forum_note_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[forum_note_min]') }}" id="forum_note_min" name="sets[forum_note_min]" value="{{ old('sets.forum_note_min', $settings['forum_note_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[forum_note_max]') }}" name="sets[forum_note_max]" value="{{ old('sets.forum_note_max', $settings['forum_note_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[forum_note_min]') }}</div>
            <div>{{ textError('sets[forum_note_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="forum_text_min" class="form-label">{{ __('settings.forum_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[forum_text_min]') }}" id="forum_text_min" name="sets[forum_text_min]" value="{{ old('sets.forum_text_min', $settings['forum_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[forum_text_max]') }}" name="sets[forum_text_max]" value="{{ old('sets.forum_text_max', $settings['forum_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[forum_text_min]') }}</div>
            <div>{{ textError('sets[forum_text_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="forum_category_min" class="form-label">{{ __('settings.forum_category_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[forum_category_min]') }}" id="forum_category_min" name="sets[forum_category_min]" value="{{ old('sets.forum_category_min', $settings['forum_category_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[forum_category_max]') }}" name="sets[forum_category_max]" value="{{ old('sets.forum_category_max', $settings['forum_category_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[forum_category_min]') }}</div>
            <div>{{ textError('sets[forum_category_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="forum_description_min" class="form-label">{{ __('settings.forum_description_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[forum_description_min]') }}" id="forum_description_min" name="sets[forum_description_min]" value="{{ old('sets.forum_description_min', $settings['forum_description_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[forum_description_max]') }}" name="sets[forum_description_max]" value="{{ old('sets.forum_description_max', $settings['forum_description_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[forum_description_min]') }}</div>
            <div>{{ textError('sets[forum_description_max]') }}</div>
        </div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[forum_merge_posts]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[forum_merge_posts]" id="forum_merge_posts"{{ getInput('sets.forum_merge_posts', $settings['forum_merge_posts']) ? ' checked' : '' }}>
        <label class="form-check-label" for="forum_merge_posts">{{ __('settings.forum_merge_posts') }}</label>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
