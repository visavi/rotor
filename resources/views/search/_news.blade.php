<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-newspaper"></i> <a href="/news" class="text-muted"> {{ __('index.news') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title d-flex align-items-baseline">
                <h3><a class="post-title" href="/news/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            @if ($post->image)
                <div class="media-file mb-3">
                    <a href="{{ $post->image }}" data-fancybox="gallery-{{ $post->id }}">{{ resizeImage($post->image, ['class' => 'img-fluid', 'alt' => $post->title]) }}</a>
                </div>
            @endif

            {{ bbCodeTruncate($post->text, 100) }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
