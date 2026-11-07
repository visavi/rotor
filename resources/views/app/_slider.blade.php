@use('App\Services\ThumbnailService')
<div class="media-file">
    <div class="slide-main">
        @php $file = $files->first(); @endphp
        <div class="slide-main-inner">
            @if ($file->isVideo())
                <video src="{{ $file->path }}" class="img-fluid rounded" controls preload="metadata"></video>
            @else
                @php
                    // Первая картинка на странице обычно LCP: грузим сразу и с высоким приоритетом.
                    // Остальные лениво, иначе на мобильном они делят канал с первой.
                    // Отметка в запросе, а не @once: лента рендерит каждый пост отдельно
                    $isFirstImage = ! request()->attributes->has('slider_image_shown');
                    request()->attributes->set('slider_image_shown', true);
                @endphp
                <a href="{{ $file->path }}" class="slide-main-link" data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}" data-slide-main>
                    <img src="{{ $file->thumbnail(ThumbnailService::MEDIA) }}" alt="{{ $file->name }}" class="img-fluid rounded slide-main-img" @if ($isFirstImage) fetchpriority="high" @else loading="lazy" @endif>
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
                       data-slide-thumb>
                        <div class="slide-thumb-video{{ $loop->first ? ' active' : '' }}">
                            <video src="{{ $file->path }}" preload="metadata" tabindex="-1"></video>
                            <span class="slide-play-icon">▶</span>
                        </div>
                    </a>
                @else
                    <a href="{{ $file->path }}"
                       class="slide-thumb-link"
                       data-fancybox="{{ $model->getMorphClass() }}-{{ $model->id }}"
                       data-preview="{{ $file->thumbnail(ThumbnailService::MEDIA) }}"
                       data-slide-thumb>
                        <img src="{{ $file->thumbnail(ThumbnailService::SQUARE) }}" alt="{{ $file->name }}" class="slide-thumb-image{{ $loop->first ? ' active' : '' }}" loading="lazy">
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
