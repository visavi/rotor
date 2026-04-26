<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-message"></i> <a href="{{ $post->relate_type }}" class="text-muted"> {{ $post->getRelateType() }}</a>
        </li>
        <li class="breadcrumb-item">
            @php
                $params = $post->relate->slug ? ['slug' => $post->relate->slug] : ['id' => $post->relate_id];
            @endphp
            <a href="{{ route($post->relate_type . '.view', $params) }}" class="text-muted">{{ $post->relate->title }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ $post->getViewUrl() }}">{{ __('main.comment') }} - {{ $post->relate->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content short-view">
        <div class="section-message">
            {{ renderHtml($post->text) }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
