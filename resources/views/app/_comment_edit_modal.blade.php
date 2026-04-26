<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil-alt"></i> {{ __('main.edit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCommentForm">
                @csrf
                <input type="hidden" id="edit-comment-id" name="id">
                <div class="modal-body">
                    <textarea class="form-control" id="edit-comment-msg" name="msg" rows="5"
                        data-relate-type="{{ \App\Models\Comment::$morphName }}"
                        data-relate-id="0"></textarea>

                    @include('app/_upload_file', ['model' => new \App\Models\Comment(), 'files' => collect(), 'showForm' => true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('main.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('main.edit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
