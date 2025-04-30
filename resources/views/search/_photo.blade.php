<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-image"></i> <a href="/photos" class="text-muted">{{ __('index.photos') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/photos/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        {{--@include('app/_carousel', ['model' => $post])--}}

        @if ($post->text)
            <div class="section-message">
                {{ bbCodeTruncate($post->text, 100) }}
            </div>
        @endif
    </div>
</div>
