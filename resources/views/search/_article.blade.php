<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="/blogs" class="text-muted">{{ __('index.blogs') }}</a>
        </li>

        <li class="breadcrumb-item">
            <a href="/blogs/{{ $post->category->id }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/articles/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ $post->shortText() }}
        </div>
    </div>
</div>
