<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-message"></i> <a href="{{ $post->relate_type }}" class="text-muted"> {{ $post->getRelateType() }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/{{ $post->relate_type }}/{{ $post->relate_id }}" class="text-muted">{{ $post->relate->title }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ $post->relate_type }}/{{ $post->relate_id }}/comments?cid={{ $post->id }}">{{ __('main.comment') }} - {{ $post->relate->title }}</a></h3>
            </div>
        </div>

        <div class="text-end section-action js-rating">
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-down{{ ($polls[$post->getMorphClass()][$post->id] ?? '') === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->rating) }}</b>
            @if ($user && $user->id !== $post->user_id)
                <a class="post-rating-up{{ ($polls[$post->getMorphClass()][$post->id] ?? '') === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ bbCode($post->text) }}
        </div>
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
