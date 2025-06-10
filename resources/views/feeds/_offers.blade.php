<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-circle-question"></i> <a href="{{ route('offers.index') }}" class="text-muted">{{ __('index.offers') }}</a>
        </li>

        <li class="breadcrumb-item">
            @if ($post->type === 'offer')
                <a href="{{ route('offers.index', ['type' => 'offer']) }}" class="text-muted">{{ __('offers.offers') }}</a>
            @else
                <a href="{{ route('offers.index', ['type' => 'issue']) }}" class="text-muted">{{ __('offers.problems') }}</a>
            @endif
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('offers.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
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
            {{ bbCode($post->text) }}
        </div>

        <div class="my-3">
            {{ $post->getStatus() }}
        </div>
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('offers.comments', ['id' => $post->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $post->count_comments }}</span>
</div>
