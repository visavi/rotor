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

        <div class="ms-2 flex-shrink-0">
            @include('app/_rating', ['model' => $post, 'vote' => $polls[$post->getMorphClass()][$post->id] ?? null])
        </div>
    </div>

    <div class="section-content short-view">
        @if ($post->getDetachedMedia()->isNotEmpty())
            @include('app/_media_slider', ['model' => $post, 'files' => $post->getDetachedMedia()])
        @endif

        <div class="section-message">
            {{ $post->getText() }}
        </div>
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
