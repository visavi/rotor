<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-download"></i> <a href="/loads" class="text-muted">{{ __('index.loads') }}</a>
        </li>

        <li class="breadcrumb-item">
            <a href="/loads/{{ $post->category_id }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('downs.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
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
