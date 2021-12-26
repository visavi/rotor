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

    <div class="mb-3{{ hasError('sets[maxblogpost]') }}">
        <label for="maxblogpost" class="form-label">{{ __('settings.blogs_symbols') }}:</label>
        <input type="number" class="form-control" id="maxblogpost" name="sets[maxblogpost]" maxlength="6" value="{{ getInput('sets.maxblogpost', $settings['maxblogpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[maxblogpost]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[blogvotepoint]') }}">
        <label for="blogvotepoint" class="form-label">{{ __('settings.blogs_points') }}:</label>
        <input type="number" class="form-control" id="blogvotepoint" name="sets[blogvotepoint]" maxlength="3" value="{{ getInput('sets.blogvotepoint', $settings['blogvotepoint']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[blogvotepoint]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[blog_create]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[blog_create]" id="blog_create"{{ getInput('sets.blog_create', $settings['blog_create']) ? ' checked' : '' }}>
        <label class="form-check-label" for="blog_create">{{ __('settings.blogs_publish') }}</label>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
