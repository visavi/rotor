<div class="js-files mb-3">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            <span class="js-file">
                @if ($file->isImage())
                    {{ resizeImage($file->hash, ['width' => 100]) }}
                @else
                    <a href="{{ $file->hash }}">{{ $file->name }}</a>
                    {{ icons($file->extension) }} {{ $file->extension }} {{ formatSize($file->size) }}
                @endif

                <a href="#" onclick="return deleteFile(this);" data-id="{{ $file->id }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" class="js-file-delete"><i class="fas fa-times"></i></a>
            </span>
        @endforeach
    @endif
</div>

<div class="js-file-template d-none">
    <span class="js-file">
        <a href="#" class="js-file-link"></a> <span class="js-file-size"></span>
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" class="js-file-delete"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="js-image-template d-none">
    <span class="js-file">
        <img src="#" width="100" alt="" class="img-fluid">
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" class="js-file-delete"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="mb-3">
    <label class="btn btn-sm btn-secondary mb-1" for="file">
        <input id="file" type="file" name="file" onchange="return submitFile(this);" data-id="{{ $id ?? 0 }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" hidden>
        {{ __('main.attach_file') }}&hellip;
    </label>
</div>

<p class="text-muted fst-italic">
    {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
    {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
    {{ __('main.valid_file_extensions') }}: {{ setting('file_extensions') }}<br>
</p>
