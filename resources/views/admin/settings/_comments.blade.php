@section('header')
    <h1>{{ trans('settings.comments') }}</h1>
@stop

<form action="/admin/settings?act=comments" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[comment_length]') }}">
        <label for="comment_length">{{ trans('settings.comments_symbols') }}:</label>
        <input type="number" class="form-control" id="comment_length" name="sets[comment_length]" maxlength="5" value="{{ getInput('sets.comment_length', $settings['comment_length']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[comment_length]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[guesttextlength]') }}">
        <label for="chatpost">{{ trans('settings.chat_per_page') }}:</label>
        <input type="number" class="form-control" id="chatpost" name="sets[chatpost]" maxlength="2" value="{{ getInput('sets.chatpost', $settings['chatpost']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[chatpost]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
