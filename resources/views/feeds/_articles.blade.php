<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="{{ route('blogs.index') }}" class="text-muted">{{ __('index.blogs') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="{{ route('blogs.blog', ['id' => $post->category->parent->id]) }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

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

        <div class="text-end section-action js-rating">
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-down{{ ($polls[$post::$morphName][$post->id] ?? '') === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->rating) }}</b>
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-up{{ ($polls[$post::$morphName][$post->id] ?? '') === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    <div class="section-content short-view">
        @if ($post->getImages()->isNotEmpty())
            @include('app/_image_viewer', ['model' => $post, 'files' => $post->getImages()])
        @endif

        <div class="section-message">
            {{ $post->getText(withImages: false) }}
        </div>
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('articles.comments', ['id' => $post->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $post->count_comments }}</span>
</div>
