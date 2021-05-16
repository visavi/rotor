<div class="modal fade" id="stickersModal" tabindex="-1" aria-labelledby="stickersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stickersModalLabel">{{ __('index.stickers') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    @foreach($stickers as $sticker)
                        <div class="col">
                            <a href="#" onclick="return pasteSticker(this);"><img src="{{ $sticker->name }}" alt="{{ $sticker->code }}" class="img-fluid" style="max-width: 50px;"></a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        pasteSticker = function (el) {
            var field = $('.markItUpEditor');
            var paste = $(el).find('img').attr('alt') + ' ';

            field.caret(paste).caret(-paste.length);

            return false;
        };

        $('#stickersModal').on('hidden.bs.modal', function () {
            $(this).remove();
        })
    </script>
</div>



