@if (! ($closed ?? false))
    @if (($comments ?? collect())->isEmpty())
        {{ showError(__('main.empty_comments')) }}
    @endif

    @if (getUser())
        <div class="section-form mb-3 shadow">
            <form action="{{ $action }}" method="post">
                @csrf
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                    <textarea class="form-control tiptap" maxlength="{{ setting('comment_text_max') }}" id="msg" rows="5" name="msg" data-relate-type="{{ \App\Models\Comment::$morphName }}" data-relate-id="0" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                @include('app/_upload_file', ['model' => new \App\Models\Comment(), 'files' => $files])

                <button class="btn btn-success">{{ __('main.write') }}</button>
            </form>
        </div>

        <a href="/rules">{{ __('main.rules') }}</a> /
        <a href="/stickers">{{ __('main.stickers') }}</a><br><br>
    @else
        {{ showError(__('main.not_authorized')) }}
    @endif
@else
    {{ showError(__('main.closed_comments')) }}
@endif
