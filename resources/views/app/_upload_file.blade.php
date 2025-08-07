@php
$files ??= $model->files;
$display = $files->isNotEmpty() || ($showForm ?? false);
@endphp

@if (! $display)
    <span class="float-end js-attach-button">
        <a href="#" onclick="return showAttachForm();">{{ __('main.attach_files') }}</a>
    </span>
@endif

<div class="js-files mb-3">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            <span class="js-file">
                @if ($file->isImage())
                    {{ resizeImage($file->path, ['class' => 'thumbnail']) }}
                @else
                    <a class="me-1" href="{{ $file->path }}">{{ $file->name }}</a>
                    {{ icons($file->extension) }} {{ $file->extension }} {{ formatSize($file->size) }}
                @endif

                <a href="#" onclick="return deleteFile(this);" data-id="{{ $file->id }}" data-type="{{ $model->getMorphClass() }}" data-token="{{ csrf_token() }}" class="js-file-delete"><i class="fas fa-times"></i></a>
            </span>
        @endforeach
    @endif
</div>

<div class="js-file-template d-none">
    <span class="js-file">
        <a href="#" class="js-file-link me-1"></a> <span class="js-file-size"></span>
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $model->getMorphClass() }}" data-token="{{ csrf_token() }}" class="js-file-delete"><i class="fas fa-times"></i></a><br>
    </span>
</div>

<div class="js-image-template d-none">
    <span class="js-file">
        <img src="" alt="" class="thumbnail">
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $model->getMorphClass() }}" data-token="{{ csrf_token() }}" class="js-file-delete"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="mb-3 js-attach-form" style="display: {{ $display ? 'block' : 'none' }};">
    <label class="btn btn-sm btn-secondary mb-1" for="file">
        <input id="file" type="file" name="file" onchange="return submitFile(this);" data-id="{{ $model->id ?? 0 }}" data-type="{{ $model->getMorphClass() }}" data-token="{{ csrf_token() }}" hidden>
        {{ __('main.attach_file') }}&hellip;
    </label>

    <div class="text-muted fst-italic">
        {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
        {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
        {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('file_extensions')) }}<br>
    </div>
</div>
