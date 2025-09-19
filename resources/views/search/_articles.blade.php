<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="{{ route('blogs.index') }}" class="text-muted">{{ __('index.blogs') }}</a>
        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('blogs.blog', ['id' => $post->category->id]) }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('articles.view', ['slug' => $post->slug]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ $post->shortText() }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
