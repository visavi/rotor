<div class="media-file">
    <div class="slide-main">
        @php $file = $files->first(); @endphp
        <a href="{{ $file->path }}" class="slide-main-link" data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}" onclick="return initSlideMainImage(this)">
            <img src="{{ $file->path }}" alt="{{ $file->name }}" class="img-fluid rounded slide-main-img">
        </a>
    </div>

    @if ($countFiles > 1)
        <div class="slide-thumbnails">
            @foreach ($files as $file)
                <a href="{{ $file->path }}" class="slide-thumb-link" data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}" onclick="return initSlideThumbImage(this)">
                    <img src="{{ $file->path }}" alt="{{ $file->name }}" class="slide-thumb-image{{ $loop->first ? ' active' : '' }}">
                </a>
            @endforeach
        </div>
    @endif
</div>
