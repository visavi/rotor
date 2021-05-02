@section('header')
    <h1>{{ __('settings.forums') }}</h1>
@stop

<form action="/admin/settings?act=forums" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[forumtem]') }}">
        <label for="forumtem">{{ __('settings.topics_per_page') }}:</label>
        <input type="number" class="form-control" id="forumtem" name="sets[forumtem]" maxlength="2" value="{{ getInput('sets.forumtem', $settings['forumtem']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumtem]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[forumpost]') }}">
        <label for="forumpost">{{ __('settings.posts_per_page') }}:</label>
        <input type="number" class="form-control" id="forumpost" name="sets[forumpost]" maxlength="2" value="{{ getInput('sets.forumpost', $settings['forumpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumpost]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[forumtextlength]') }}">
        <label for="forumtextlength">{{ __('settings.posts_symbols') }}:</label>
        <input type="number" class="form-control" id="forumtextlength" name="sets[forumtextlength]" maxlength="5" value="{{ getInput('sets.forumtextlength', $settings['forumtextlength']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumtextlength]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
