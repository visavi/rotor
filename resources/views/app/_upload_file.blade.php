@php
$files ??= $model->files;
$display = $files->isNotEmpty() || ($showForm ?? false);
@endphp

@if (! $display)
    <span class="float-end js-attach-button">
        <a href="#" data-reveal=".js-attach-form" data-reveal-hide=".js-attach-button">{{ __('main.attach_files') }}</a>
    </span>
@endif

{{-- Файлы перетаскиваются: порядок сразу уходит на сервер, форма записи его не несёт --}}
<div class="js-files mb-3" data-sortable data-sortable-url="/ajax/file/sort?type={{ $model->getMorphClass() }}">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            <span class="js-file" data-key="{{ $file->id }}">
                @if ($file->isVideo())
                    <span class="thumbnail-wrap sortable-handle" data-sortable-handle title="{{ __('main.drag_reorder') }}">
                        <video src="{{ $file->path }}" class="thumbnail" preload="metadata"></video>
                        <span class="slide-play-icon">▶</span>
                    </span>
                @elseif ($file->isImage())
                    <span class="thumbnail-wrap sortable-handle" data-sortable-handle title="{{ __('main.drag_reorder') }}"><img src="{{ $file->path }}" class="thumbnail" alt="{{ $file->name }}"></span>
                @else
                    <span class="text-muted sortable-handle" data-sortable-handle title="{{ __('main.drag_reorder') }}"><i class="fas fa-grip-vertical"></i></span>
                    <a class="me-1" href="{{ $file->path }}">{{ $file->name }}</a>
                    {{ icons($file->extension) }} {{ $file->extension }} {{ formatSize($file->size) }}
                @endif

                <a href="#" onclick="return deleteFile(this);" data-id="{{ $file->id }}" data-type="{{ $model->getMorphClass() }}" class="js-file-delete"><i class="fas fa-times"></i></a>
            </span>
        @endforeach
    @endif
</div>

<div class="js-file-template d-none">
    <span class="js-file">
        <span class="text-muted sortable-handle" data-sortable-handle title="{{ __('main.drag_reorder') }}"><i class="fas fa-grip-vertical"></i></span>
        <a href="#" class="js-file-link me-1"></a> <span class="js-file-size"></span>
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $model->getMorphClass() }}" class="js-file-delete"><i class="fas fa-times"></i></a><br>
    </span>
</div>

<div class="js-image-template d-none">
    <span class="js-file">
        <span class="thumbnail-wrap sortable-handle" data-sortable-handle title="{{ __('main.drag_reorder') }}"><img src="" alt="" class="thumbnail"></span>
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $model->getMorphClass() }}" class="js-file-delete"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="mb-3 js-attach-form" style="display: {{ $display ? 'block' : 'none' }};">
    <label class="btn btn-sm btn-secondary mb-1">
        <input type="file" name="file" multiple data-max="{{ setting('maxfiles') }}" data-max-message="{{ __('validator.files_max', ['max' => setting('maxfiles')]) }}" onchange="return submitFile(this);" data-id="{{ $model->id ?? 0 }}" data-type="{{ $model->getMorphClass() }}" hidden>
        {{ __('main.attach_file') }}&hellip;
    </label>

    <div class="text-muted fst-italic">
        {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
        {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
        {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('file_extensions')) }}<br>
    </div>
</div>
