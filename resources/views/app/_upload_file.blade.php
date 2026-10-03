@php
$files ??= $model->files;
// Форма со своей кнопкой выбора файлов (скрепка в форме ответа) передаёт id поля — ссылки тогда нет.
// Имя редкое: @include наследует переменные родителя
$ownPicker = isset($attachInputId);
// На странице бывает несколько форм с вложениями (комментарий и его правка в модалке)
$attachInputId ??= 'attach-' . uniqid();
// Список по типу записи — тот же, что проверит сервер
$extensions = \App\Services\FileService::extensions($model->getMorphClass());
// Окно выбора показывает только допустимые файлы
$accept = \App\Services\FileService::accept($model->getMorphClass());
$limits = __('main.attach_limit', [
    'files' => plural(setting('maxfiles'), __('main.attach_limit_files')),
    'size'  => formatSize(setting('filesize')),
]) . ': ' . implode(', ', $extensions);
@endphp

<input type="file" id="{{ $attachInputId }}" name="file" multiple @if ($accept) accept="{{ $accept }}" @endif data-max="{{ setting('maxfiles') }}" data-max-message="{{ __('validator.files_max', ['max' => setting('maxfiles')]) }}" onchange="return submitFile(this);" data-id="{{ $model->id ?? 0 }}" data-type="{{ $model->getMorphClass() }}" hidden>

@unless ($ownPicker)
    {{-- Ссылка сразу открывает выбор файлов, лимиты в подсказке --}}
    <label for="{{ $attachInputId }}" class="float-end link-primary cursor-pointer" title="{{ $limits }}">
        <i class="fas fa-paperclip"></i> {{ __('main.attach_files') }}
    </label>
@endunless

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

{{-- Видна, только пока прикреплены файлы (CSS): подсказку на телефоне не навести --}}
<div class="attach-limits small text-muted fst-italic mb-3">{{ $limits }}</div>

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
