<dialog class="dialog dialog-lg dialog-top" id="editCommentDialog" aria-labelledby="editCommentDialogTitle">
    <div class="dialog-header">
        <h5 class="dialog-title" id="editCommentDialogTitle"><i class="fa fa-pencil-alt"></i> {{ __('main.edit') }}</h5>
        <button type="button" class="btn-close" data-dialog-close aria-label="{{ __('main.close') }}"></button>
    </div>
    <form id="editCommentForm">
        @csrf
        <input type="hidden" id="edit-comment-id" name="id">
        {{-- Раскладка как у формы добавления: кнопка слева на уровне ссылки на файлы --}}
        <div class="dialog-body">
            <div class="mb-3">
                <textarea class="form-control" id="edit-comment-msg" name="msg" rows="5"
                    data-relate-type="{{ \App\Models\Comment::$morphName }}"
                    data-relate-id="0"></textarea>
            </div>

            @include('app/_upload_file', ['model' => new \App\Models\Comment(), 'files' => collect()])

            <button type="submit" class="btn btn-primary">{{ __('main.edit') }}</button>
        </div>
    </form>
</dialog>
