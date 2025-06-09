<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-message"></i> <a class="text-muted" href="{{ route('forums.index') }}"> {{ __('index.forums') }}</a>
        </li>
        <li class="breadcrumb-item">
            <a class="text-muted" href="{{ route('topics.topic', ['id' => $post->topic->id]) }}">{{ $post->topic->title }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('topics.topic', ['id' => $post->topic->id, 'pid' => $post->id]) }}">{{ $post->topic->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ bbCodeTruncate($post->text, 100) }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
