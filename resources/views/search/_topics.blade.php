<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-message"></i> <a href="{{ route('forums.index') }}" class="text-muted"> {{ __('index.forums') }}</a>
        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('forums.forum', ['id' => $post->forum->id]) }}" class="text-muted">{{ $post->forum->title }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('topics.topic', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content short-view">
        <div class="section-message">
            {{ $post->lastPost->text ? renderHtml($post->lastPost->text) : __('main.deleted') }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
