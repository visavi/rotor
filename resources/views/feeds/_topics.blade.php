<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-message"></i> <a href="{{ route('forums.index') }}" class="text-muted"> {{ __('index.forums') }}</a>
        </li>
        @if ($post->forum->parent->id)
            <li class="breadcrumb-item">
                <a href="{{ route('forums.forum', ['id' => $post->forum->parent->id]) }}" class="text-muted">{{ $post->forum->parent->title }}</a>
            </li>
        @endif

        <li class="breadcrumb-item">
            <a href="{{ route('forums.forum', ['id' => $post->forum->id]) }}" class="text-muted">{{ $post->forum->title }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('topics.topic', ['id' => $post->id, 'pid' => $post->lastPost->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>

        <div class="text-end section-action js-rating">
            @if ($user && $user->id !== $post->lastPost->user_id)
                <a class="post-rating-down{{ ($polls[$post->lastPost::$morphName][$post->lastPost->id] ?? '') === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->lastPost->id }}" data-type="{{ $post->lastPost->getMorphClass() }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->lastPost->rating) }}</b>
            @if ($user && $user->id !== $post->lastPost->user_id)
                <a class="post-rating-up{{ ($polls[$post->lastPost::$morphName][$post->lastPost->id] ?? '') === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->lastPost->id }}" data-type="{{ $post->lastPost->getMorphClass() }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    <div class="section-content short-view">
        @if ($post->lastPost->getImages()->isNotEmpty())
            @include('app/_image_viewer', ['model' => $post, 'files' => $post->lastPost->getImages()])
        @endif

        <div class="section-message">
            {{ $post->lastPost->text ? $post->lastPost->getText() : 'Удалено' }}
        </div>

        @if ($post->lastPost->getFiles()->isNotEmpty())
            @foreach ($post->lastPost->getFiles() as $file)
                <div class="media-file">
                    @if ($file->isVideo())
                        <div>
                            <video src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls playsinline></video>
                        </div>
                    @endif

                    @if ($file->isAudio())
                        <div>
                            <audio src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls></audio>
                        </div>
                    @endif

                    {{ icons($file->extension) }}
                    <a href="{{ $file->path }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})
                </div>
            @endforeach
        @endif
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->lastPost->user->getAvatarImage() }}</span> {{ $post->lastPost->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->lastPost->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('topics.topic', ['id' => $post->id]) }}">{{ __('main.messages') }}</a> <span class="badge bg-adaptive">{{ $post->count_posts }}</span>
</div>
