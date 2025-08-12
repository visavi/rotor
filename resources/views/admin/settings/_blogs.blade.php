@section('header')
    <h1>{{ __('settings.blogs') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[blogpost]') }}">
        <label for="blogpost" class="form-label">{{ __('settings.blogs_per_page') }}:</label>
        <input type="number" class="form-control" id="blogpost" name="sets[blogpost]" maxlength="2" value="{{ getInput('sets.blogpost', $settings['blogpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blogpost]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[bloggroup]') }}">
        <label for="bloggroup" class="form-label">{{ __('settings.blogs_groups') }}:</label>
        <input type="number" class="form-control" id="bloggroup" name="sets[bloggroup]" maxlength="2" value="{{ getInput('sets.bloggroup', $settings['bloggroup']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[bloggroup]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[blog_point]') }}">
        <label for="blog_point" class="form-label">{{ __('settings.blog_point') }}:</label>
        <input type="number" class="form-control" id="blog_point" name="sets[blog_point]" maxlength="2" value="{{ getInput('sets.blog_point', $settings['blog_point']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blog_point]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[blog_money]') }}">
        <label for="blog_money" class="form-label">{{ __('settings.blog_money') }}:</label>
        <input type="number" class="form-control" id="blog_money" name="sets[blog_money]" maxlength="2" value="{{ getInput('sets.blog_money', $settings['blog_money']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blog_money]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[blog_create]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[blog_create]" id="blog_create"{{ getInput('sets.blog_create', $settings['blog_create']) ? ' checked' : '' }}>
        <label class="form-check-label" for="blog_create">{{ __('settings.blogs_publish') }}</label>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[article_moderation]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[article_moderation]" id="article_moderation"{{ getInput('sets.article_moderation', $settings['article_moderation']) ? ' checked' : '' }}>
        <label class="form-check-label" for="article_moderation">{{ __('settings.blog_moderation') }}</label>
    </div>

    <div class="mb-3">
        <label for="blog_title_min" class="form-label">{{ __('settings.blog_title_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[blog_title_min]') }}" id="blog_title_min" name="sets[blog_title_min]" value="{{ old('sets.blog_title_min', $settings['blog_title_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[blog_title_max]') }}" name="sets[blog_title_max]" value="{{ old('sets.blog_title_max', $settings['blog_title_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[blog_title_min]') }}</div>
            <div>{{ textError('sets[blog_title_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="blog_text_min" class="form-label">{{ __('settings.blog_text_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[blog_text_min]') }}" id="blog_text_min" name="sets[blog_text_min]" value="{{ old('sets.blog_text_min', $settings['blog_text_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[blog_text_max]') }}" name="sets[blog_text_max]" value="{{ old('sets.blog_text_max', $settings['blog_text_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[blog_text_min]') }}</div>
            <div>{{ textError('sets[blog_text_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="blog_tag_min" class="form-label">{{ __('settings.blog_tag_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[blog_tag_min]') }}" id="blog_tag_min" name="sets[blog_tag_min]" value="{{ old('sets.blog_tag_min', $settings['blog_tag_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[blog_tag_max]') }}" name="sets[blog_tag_max]" value="{{ old('sets.blog_tag_max', $settings['blog_tag_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[blog_tag_min]') }}</div>
            <div>{{ textError('sets[blog_tag_max]') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="blog_category_min" class="form-label">{{ __('settings.blog_category_length') }}:</label>
        <div class="d-flex gap-2">
            <input type="number" class="form-control{{ hasError('sets[blog_category_min]') }}" id="blog_category_min" name="sets[blog_category_min]" value="{{ old('sets.blog_category_min', $settings['blog_category_min']) }}" placeholder="{{ __('main.min') }}" required>
            <input type="number" class="form-control{{ hasError('sets[blog_category_max]') }}" name="sets[blog_category_max]" value="{{ old('sets.blog_category_max', $settings['blog_category_max']) }}" placeholder="{{ __('main.max') }}" required>
        </div>
        <div class="invalid-feedback d-block">
            <div>{{ textError('sets[blog_category_min]') }}</div>
            <div>{{ textError('sets[blog_category_max]') }}</div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
