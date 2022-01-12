<form method="post" enctype="multipart/form-data">
    @csrf
    <div class="mb-3{{ hasError('category') }}">
        <label for="inputCategory" class="form-label">{{ __('loads.load') }}:</label>

        <select class="form-select" id="inputCategory" name="category">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"{{ ($cid === $category->id || $down->category_id === $category->id) && ! $category->closed ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <option value="{{ $child->id }}"{{ ($cid === $child->id || $down->category_id === $child->id) && ! $child->closed ? ' selected' : '' }}{{ $child->closed ? ' disabled' : '' }}>– {{ $child->name }}</option>
                    @endforeach
                @endif
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('category') }}</div>
    </div>

    <div class="mb-3{{ hasError('title') }}">
        <label for="title" class="form-label">{{ __('loads.down_title') }}:</label>
        <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $down->title) }}" required>
        <div class="invalid-feedback">{{ textError('title') }}</div>
    </div>

    <div class="mb-3{{ hasError('text') }}">
        <label for="text" class="form-label">{{ __('loads.down_text') }}:</label>
        <textarea class="form-control markItUp" id="text" name="text" rows="5" maxlength="5000">{{ getInput('text', $down->text) }}</textarea>
        <div class="invalid-feedback">{{ textError('text') }}</div>
        <span class="js-textarea-counter"></span>
    </div>

    @if ($down->getFiles()->isNotEmpty())
        @foreach ($down->getFiles() as $file)
            <div class="media-file mb-3">
                <i class="fa fa-download"></i>
                <b><a href="{{ $file->hash }}">{{ $file->name }}</a></b> ({{ formatSize($file->size) }}) (<a href="/downs/delete/{{ $down->id }}/{{ $file->id }}" onclick="return confirm('{{ __('loads.confirm_delete_file') }}')">{{ __('main.delete') }}</a>)
            </div>
        @endforeach
    @endif

    @if ($down->getImages()->isNotEmpty())
        @foreach ($down->getImages() as $image)
            <div class="media-file mb-3">
                {{ resizeImage($image->hash) }}<br>
                <i class="fa fa-image"></i> <b><a href="{{ $image->hash }}">{{ $image->name }}</a></b> ({{ formatSize($image->size ) }}) (<a href="/downs/delete/{{ $down->id }}/{{ $image->id }}" onclick="return confirm('{{ __('loads.confirm_delete_screen') }}')">{{ __('main.delete') }}</a>)
            </div>
        @endforeach
    @endif

    @if (setting('down_allow_links'))
        <span class="float-end">
            <a class="js-links-add" href="#">{{ __('loads.add_link') }}</a>
        </span>
    @endif

    <?php $links = array_values(array_diff((array) getInput('links', $down->links), [''])) ?>

    @if ($down->files->count() + count($links) < setting('maxfiles'))
        <div class="mb-3{{ hasError('files') }}">
            <label class="btn btn-sm btn-secondary" for="files">
                <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ __('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                {{ __('main.attach_files') }}&hellip;
            </label>
            <span class="badge bg-info" id="upload-file-info"></span>
            <div class="invalid-feedback">{{ textError('files') }}</div>
        </div>
    @endif

    @if (setting('down_allow_links'))
        <div class="mb-3{{ hasError('links') }}">
            <div class="js-links-list">
                @for ($i = 0, $countLinks = count($links); $i < $countLinks; $i++)
                    <div class="input-group mt-1 js-links-append">
                        <input class="form-control" name="links[]" type="text" value="{{ $links[$i] ?? '' }}" maxlength="100" placeholder="https://">
                        <span class="input-group-text">
                            <a class="js-links-remove" href="#"><i class="fa fa-times"></i></a>
                        </span>
                    </div>
                @endfor
            </div>
            <div class="invalid-feedback">{{ textError('links') }}</div>
        </div>
    @endif

    <p class="text-muted fst-italic">
        {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
        {{ __('main.max_file_weight') }}: {{ formatSize(setting('fileupload')) }}<br>
        {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
        {{ __('main.min_image_size') }}: 100px
    </p>

    <button class="btn btn-primary">{{ $down->id ? __('main.edit') : __('main.upload') }}</button>
</form>

@push('scripts')
    <script>
        $('.js-links-add').click(function () {
            const listBlock =  $('.js-links-list');

            listBlock.append('<div class="input-group mt-1 js-links-append">' +
                '<input class="form-control" name="links[]" type="text" value="" maxlength="100" placeholder="https://">' +
                '<span class="input-group-text">' +
                '<a class="js-links-remove" href="#"><i class="fa fa-times"></i></a>' +
                '</span>' +
                '</div>');

            const inputs = listBlock.find($('input') );
            if (inputs.length >= <?= (setting('maxfiles') - $down->files->count()) ?>) {
                $('.js-links-add').hide();
            }

            return false;
        });

        $(document).on('click', '.js-links-remove', function () {
            $(this).closest('.js-links-append').remove();
            $('.js-links-add').show();

            return false;
        });
    </script>
@endpush
