<i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->getName() }}</b>
<small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>

<div class="section-form mb-3 shadow">
    <form action="{{ $action }}" method="post">
        @csrf
        <div class="mb-3{{ hasError('msg') }}">
            <label for="msg" class="form-label">{{ __('main.message') }}:</label>
            <textarea class="form-control tiptap" maxlength="{{ setting('comment_text_max') }}" id="msg" rows="5" name="msg" data-relate-type="{{ $comment->getMorphClass() }}" data-relate-id="{{ $comment->id }}" required>{{ getInput('msg', $comment->text) }}</textarea>
            <div class="invalid-feedback">{{ textError('msg') }}</div>
            <span class="js-textarea-counter"></span>
        </div>

        @include('app/_upload_file', ['model' => $comment])

        <button class="btn btn-primary">{{ __('main.edit') }}</button>
    </form>
</div>
