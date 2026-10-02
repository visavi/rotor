@if (! ($closed ?? false))
    @if (($comments ?? collect())->isEmpty())
        {{ showError(__('main.empty_comments')) }}
    @endif

    @if (getUser())
        @php
            // Свёрнута в одно поле, пока нечего показывать: после ошибки или с прикреплёнными файлами открыта
            $compact = ! $errors->any() && ! old('msg') && $files->isEmpty();
        @endphp

        <form action="{{ $action }}" method="post" class="mb-3"@if ($compact) data-compact @endif>
            @csrf
            <div class="mb-3 compact-field{{ hasError('msg') }}">
                <textarea class="form-control tiptap" maxlength="{{ setting('comment_text_max') }}" id="msg" rows="5" name="msg" data-relate-type="{{ \App\Models\Comment::$morphName }}" data-relate-id="0" placeholder="{{ __('main.write_comment') }}" required>{{ old('msg') }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @include('app/_upload_file', ['model' => new \App\Models\Comment(), 'files' => $files])

            <button class="btn btn-success">{{ __('main.write') }}</button>
        </form>
    @else
        {{ showError(__('main.not_authorized')) }}
    @endif
@else
    {{ showError(__('main.closed_comments')) }}
@endif
