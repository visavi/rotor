<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="/blogs" class="text-muted">{{ __('index.blogs') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="/blogs/{{ $post->category->parent->id }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

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

        <div class="text-end js-rating">
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
            {{ $post->shortText() }}
        </div>

        <div class="section-body">
            <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
            <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
        </div>

        <i class="fa-regular fa-comment"></i> <a href="/articles/comments/{{ $post->id }}">{{ __('main.comments') }}</a> ({{ $post->count_comments }})
        <a href="/articles/end/{{ $post->id }}">&raquo;</a>
    </div>
</div>
