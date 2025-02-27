<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-newspaper"></i> <a href="/news" class="text-muted"> {{ __('index.news') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/news/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>

        <div class="js-rating text-end">
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-down{{ ($polls[$post::$morphName][$post->id] ?? '') === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->rating) }}</b>
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-up{{ ($polls[$post::$morphName][$post->id] ?? '') === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            @if ($post->image)
                <div class="media-file mb-3">
                    <a href="{{ $post->image }}" data-fancybox="gallery-{{ $post->id }}">{{ resizeImage($post->image, ['class' => 'img-thumbnail img-fluid', 'alt' => $post->title]) }}</a>
                </div>
            @endif

            {{ bbCode($post->text) }}
        </div>
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="/news/comments/{{ $post->id }}">{{ __('main.comments') }}</a> ({{ $post->count_comments }})
    <a href="/news/end/{{ $post->id }}">&raquo;</a>
</div>
