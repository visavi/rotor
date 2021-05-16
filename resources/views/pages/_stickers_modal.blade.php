<div class="modal fade" id="stickersModal" tabindex="-1" aria-labelledby="stickersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stickersModalLabel">{{ __('index.stickers') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row mb-3">
                    @foreach($stickers as $sticker)
                        <div class="col-md-3 col-sm-6">
                            <a href="#" onclick="return pasteSticker(this);" class=""><img src="{{ $sticker->name }}" alt="{{ $sticker->code }}" class="img-fluid"></a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        pasteSticker = function (el) {
            var field    = $('.markItUpEditor');
            var caretPos = field[0].selectionStart;
            var text     = field.val();
            var paste    = ' ' + $(el).find('img').attr('alt') + ' ';

            field.val(text.substring(0, caretPos) + paste + text.substring(caretPos));

            return false;
        };

        $('#stickersModal').on('hidden.bs.modal', function () {
            $(this).remove();
        })
    </script>
</div>



