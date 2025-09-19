<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-image"></i> <a href="{{ route('photos.index') }}" class="text-muted">{{ __('index.photos') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('photos.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>

        <div class="text-end section-action js-rating">
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
        @include('app/_image_viewer', ['model' => $post])

        @if ($post->text)
            <div class="section-message">
                {{ bbCode($post->text) }}
            </div>
        @endif
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('photos.comments', ['id' => $post->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $post->count_comments }}</span>
</div>
