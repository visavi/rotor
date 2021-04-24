<div class="js-files mb-3">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            @if ($file->isImage())
                <div class="js-file d-inline-block">
                    {{ resizeImage($file->hash, ['width' => 100]) }}
                </div>
            @else
                <div class="js-file d-inline-block" style="
                     font-weight: bold;
                     background: #eee;
                     border: 1px solid #ccc;
                     padding:10px 3px;
                     width:100px;
                ">
                    <a href="{{ $file->hash }}">{{ $file->name }}</a>
                    {{ icons($file->extension) }} {{ $file->extension }} {{ formatSize($file->size) }}
                </div>
            @endif

            @if (! $file->relate_id)
                <a href="#" onclick="return deleteFile(this);" data-id="{{ $file->id }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" class="js-file-delete"><i class="fas fa-times"></i></a>
            @endif
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
    <span class="js-image">
        <img src="#" width="100" alt="" class="img-fluid">
        <a href="#" onclick="return deleteFile(this);" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" class="js-file-delete"><i class="fas fa-times"></i></a>
    </span>
</div>

<div class="mb-3">
    <label class="btn btn-sm btn-secondary mb-1" for="file">
        <input id="file" type="file" name="image" onchange="return submitFile(this);" data-id="{{ $id ?? 0 }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" hidden>
        {{ __('main.attach_file') }}&hellip;
    </label>
</div>

<p class="text-muted font-italic">
    {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
    {{ __('main.max_file_weight') }}: {{ formatSize(setting('forumloadsize')) }}<br>
    {{ __('main.valid_file_extensions') }}: {{ setting('forumextload') }}<br>
</p>
