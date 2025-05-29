<div class="media-file">
    <div class="slide-main">
        @php
            $file = $files->first();
            $image = resizeImage($file->path, [
                'alt' => $model->title,
                'class' => 'img-fluid rounded slide-main-img',
            ]);
        @endphp

        <a href="{{ $file->path }}" class="slide-main-link" data-fancybox="gallery-{{ $model->id }}" onclick="return initSlideMainImage(this)">{{ $image }}</a>
    </div>

    @if ($countFiles > 1)
        <div class="slide-thumbnails">
            @foreach ($files as $file)
                @php
                    $isActive = $loop->first ? ' active' : '';
                    $image = resizeImage($file->path, [
                        'alt' => $model->title,
                        'class' => 'slide-thumb-image' . $isActive,
                    ]);
                @endphp

                <a href="{{ $file->path }}" class="slide-thumb-link" data-fancybox="gallery-{{ $model->id }}" onclick="return initSlideThumbImage(event, this)">{{ $image }}</a>
            @endforeach
        </div>
    @endif
</div>
