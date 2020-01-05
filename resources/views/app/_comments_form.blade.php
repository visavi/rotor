@if (empty($closed))
    @if (getUser())
        <div class="form">
            <form method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text ?? null) }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                <button class="btn btn-success">{{ isset($comment) ? __('main.edit') : __('main.write') }}</button>
            </form>
        </div><br>


        <a href="/rules">{{ __('main.rules') }}</a> /
        <a href="/stickers">{{ __('main.stickers') }}</a> /
        <a href="/tags">{{ __('main.tags') }}</a><br><br>
    @else
        {!! showError(__('main.not_authorized')) !!}
    @endif
@else
    {!! showError(__('main.closed_comments')) !!}
@endif
