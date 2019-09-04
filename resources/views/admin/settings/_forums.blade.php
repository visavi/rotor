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

    <div class="form-group{{ hasError('sets[forumloadsize]') }}">
        <label for="forumloadsize">{{ __('main.max_file_weight') }} (Mb):</label>
        <input type="number" class="form-control" id="forumloadsize" name="sets[forumloadsize]" maxlength="2" value="{{ getInput('sets.forumloadsize', round($settings['forumloadsize'] / 1048576)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumloadsize]') }}</div>

        <input type="hidden" value="1048576" name="mods[forumloadsize]">
        <span class="text-muted font-italic">{{ __('main.server_limit') }}: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[forumextload]') }}">
        <label for="forumextload">{{ __('main.valid_file_extensions') }}:</label>
        <textarea class="form-control" id="forumextload" name="sets[forumextload]" required>{{ getInput('sets.forumextload', $settings['forumextload']) }}</textarea>
        <div class="invalid-feedback">{{ textError('sets[forumextload]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[forumloadpoints]') }}">
        <label for="forumloadpoints">{{ __('settings.forums_upload_points') }}:</label>
        <input type="number" class="form-control" id="forumloadpoints" name="sets[forumloadpoints]" maxlength="4" value="{{ getInput('sets.forumloadpoints', $settings['forumloadpoints']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[forumloadpoints]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
