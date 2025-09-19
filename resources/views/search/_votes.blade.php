<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="{{ route('votes.index') }}" class="text-muted">{{ __('index.votes') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('votes.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            @if ($post->description)
                {{ bbCodeTruncate($post->description, 100) }}
            @endif
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
