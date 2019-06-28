<?php
$cond  = empty($paste) ? false : true;
$click = $cond ? 'return pasteImage(this);' : false;
?>

<div class="js-images">
    @if ($files->isNotEmpty())
        @foreach ($files as $file)
            <span class="js-image">
                {!! resizeImage($file->hash, ['width' => 100, 'onclick' => $click]) !!}
                @if (! $file->relate_id)
                    <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                @endif
            </span>
        @endforeach
    @endif
</div>

<div class="js-image-template d-none">
    <span class="js-image">
        <img src="#" width="100" onclick="{{ $click }}" alt="" class="img-fluid">
        <a href="#" onclick="return deleteImage(this);" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
    </span>
</div>

<label class="btn btn-sm btn-secondary" for="image">
    <input id="image" type="file" name="image" accept="image/*" onchange="return submitImage(this, {{ $cond }});" data-id="{{ $id ?? 0 }}" data-type="{{ $type }}" data-token="{{ $_SESSION['token'] }}" hidden>
    {{ trans('main.attach_image') }}&hellip;
</label><br>

<p class="text-muted font-italic">
    {{ trans('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
    {{ trans('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
    {{ trans('main.valid_file_extensions') }}: jpg, jpeg, gif, png<br>
    {{ trans('main.min_image_size') }}: 100px
</p>
