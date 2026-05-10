<div class="media-file">
    <div class="slide-main">
        @php $file = $files->first(); @endphp
        <div class="slide-main-inner">
            @if ($file->isVideo())
                <video src="{{ $file->path }}" class="img-fluid rounded" controls preload="metadata"></video>
            @else
                <a href="{{ $file->path }}" class="slide-main-link" data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}" onclick="return initSlideMainImage(this)">
                    <img src="{{ $file->path }}" alt="{{ $file->name }}" class="img-fluid rounded slide-main-img">
                </a>
            @endif
        </div>
    </div>

    @if ($countFiles > 1)
        <div class="slide-thumbnails">
            @foreach ($files as $file)
                @if ($file->isVideo())
                    <a href="{{ $file->path }}"
                       class="slide-thumb-link"
                       data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}"
                       data-type="html5video"
                       aria-label="{{ $file->name }}"
                       onclick="return initSlideThumbImage(this)">
                        <div class="slide-thumb-video{{ $loop->first ? ' active' : '' }}">
                            <video src="{{ $file->path }}" preload="metadata" tabindex="-1"></video>
                            <span class="slide-play-icon">▶</span>
                        </div>
                    </a>
                @else
                    <a href="{{ $file->path }}"
                       class="slide-thumb-link"
                       data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}"
                       onclick="return initSlideThumbImage(this)">
                        <img src="{{ $file->path }}" alt="{{ $file->name }}" class="slide-thumb-image{{ $loop->first ? ' active' : '' }}">
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
