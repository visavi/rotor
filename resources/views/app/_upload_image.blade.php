@php
$files ??= $model->files;
$click = empty($paste) ? null : 'return pasteImage(this);';
$pointer = empty($paste) ? null : 'cursor-pointer';
@endphp

<div class="js-files mb-3">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            <span class="js-file">
                <span onclick="{{ $click }}" class="{{ $pointer }}">{{ resizeImage($file->path, ['class' => 'thumbnail']) }}</span>
                <a href="#" onclick="return deleteFile(this);" data-id="{{ $file->id }}" data-type="{{ $model->getMorphClass() }}"><i class="fas fa-times"></i></a>
            </span>
        @endforeach
    @endif
</div>

<div class="js-image-template d-none">
    <span class="js-file">
        <span onclick="{{ $click }}" class="{{ $pointer }}"><img src="" alt="" class="thumbnail"></span>
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $model->getMorphClass() }}"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="mb-3">
    <label for="file" class="btn btn-sm btn-secondary mb-1 form-label">
        <input id="file" type="file" name="file" accept="image/*" onchange="return submitImage(this, {{ $paste ?? false }});" data-id="{{ $model->id ?? 0 }}" data-type="{{ $model->getMorphClass() }}" hidden>
        {{ __('main.attach_image') }}&hellip;
    </label>
</div>

<p class="text-muted fst-italic">
    {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
    {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
    {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('image_extensions')) }}<br>
    {{ __('main.min_image_size') }}: 100px
</p>
