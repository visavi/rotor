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

    <div class="mb-3{{ hasError('sets[forumtextlength]') }}">
        <label for="forumtextlength" class="form-label">{{ __('settings.posts_symbols') }}:</label>
        <input type="number" class="form-control" id="forumtextlength" name="sets[forumtextlength]" maxlength="5" value="{{ getInput('sets.forumtextlength', $settings['forumtextlength']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumtextlength]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
