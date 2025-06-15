<form method="post">
    @csrf
    <div class="mb-3{{ hasError('category') }}">
        <label for="inputCategory" class="form-label">{{ __('loads.load') }}:</label>

        <select class="form-select" id="inputCategory" name="category">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"{{ $cid === $category->id || $down->category_id === $category->id ? ' selected' : '' }}{{ $category->closed && $category->id !== $down->category_id ? ' disabled' : '' }}>
                    {{ str_repeat('–', $category->depth) }} {{ $category->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('category') }}</div>
    </div>

    <div class="mb-3{{ hasError('title') }}">
        <label for="title" class="form-label">{{ __('loads.down_title') }}:</label>
        <input class="form-control" name="title" id="title" maxlength="{{ setting('down_title_max') }}" value="{{ getInput('title', $down->title) }}" required>
        <div class="invalid-feedback">{{ textError('title') }}</div>
    </div>

    <div class="mb-3{{ hasError('text') }}">
        <label for="text" class="form-label">{{ __('loads.down_text') }}:</label>
        <textarea class="form-control markItUp" id="text" name="text" rows="5" maxlength="{{ setting('down_text_max') }}">{{ getInput('text', $down->text) }}</textarea>
        <div class="invalid-feedback">{{ textError('text') }}</div>
        <span class="js-textarea-counter"></span>
    </div>

    @if (setting('down_allow_links'))
        <a class="mb-3 ms-3 float-end js-links-add" href="#">{{ __('loads.add_link') }}</a>
    @endif

    @php $links = array_values(array_diff((array) getInput('links', $down->links), [''])); @endphp
    @if (setting('down_allow_links'))
        <div class="mb-3{{ hasError('links') }}">
            <div class="js-links-list">
                @for ($i = 0, $countLinks = count($links); $i < $countLinks; $i++)
                    <div class="input-group mt-1 js-links-append">
                        <input class="form-control" name="links[]" type="text" value="{{ $links[$i] ?? '' }}" maxlength="{{ setting('down_link_max') }}" placeholder="https://">
                        <span class="input-group-text">
                            <a class="js-links-remove" href="#"><i class="fa fa-times"></i></a>
                        </span>
                    </div>
                @endfor
            </div>
            <div class="invalid-feedback">{{ textError('links') }}</div>
        </div>
    @endif

    @include('app/_upload_file', [
        'model'    => $down,
        'showForm' => true,
    ])

    <button class="btn btn-primary">{{ $down->id ? __('main.edit') : __('main.upload') }}</button>
</form>

@push('scripts')
    <script>
        $('.js-links-add').click(function () {
            const listBlock = $('.js-links-list');

            listBlock.append('<div class="input-group mt-1 js-links-append">' +
                '<input class="form-control" name="links[]" type="text" value="" maxlength="<?= setting('down_category_max') ?>" placeholder="https://">' +
                '<span class="input-group-text">' +
                '<a class="js-links-remove" href="#"><i class="fa fa-times"></i></a>' +
                '</span>' +
                '</div>');

            const inputs = listBlock.find('input');
            if (inputs.length >= <?= (setting('maxfiles') - $down->files->count()) ?>) {
                $('.js-links-add').hide();
            }

            return false;
        });

        $(document).on('click', '.js-links-remove', function () {
            $(this).closest('.js-links-append').remove();

            const remainingInputs = $('.js-links-list').find('input').length;
            if (remainingInputs < <?= (setting('maxfiles') - $down->files->count()) ?>) {
                $('.js-links-add').show();
            }

            return false;
        });
    </script>
@endpush
