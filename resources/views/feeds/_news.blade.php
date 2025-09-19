<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-newspaper"></i> <a href="{{ route('news.index') }}" class="text-muted"> {{ __('index.news') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title d-flex align-items-baseline">
                <h3><a class="post-title" href="{{ route('news.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>

                @if ($post->top)
                    <span class="ms-2" data-bs-toggle="tooltip" title="{{ __('main.pinned') }}"><i class="fa-solid fa-thumbtack fa-xs"></i></span>
                @endif
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
        @if ($post->getImages()->isNotEmpty())
            @include('app/_image_viewer', ['model' => $post, 'files' => $post->getImages()])
        @endif

        <div class="section-message">
            {{ $post->shortText() }}
        </div>

        @if ($post->getFiles()->isNotEmpty())
            @foreach ($post->getFiles() as $file)
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
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('news.comments', ['id' => $post->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $post->count_comments }}</span>
</div>
