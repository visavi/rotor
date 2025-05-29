<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-download"></i> <a href="/loads" class="text-muted">{{ __('index.loads') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="/loads/{{ $post->category->parent->id }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

        <li class="breadcrumb-item">
            <a href="/loads/{{ $post->category_id }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/downs/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>

        <div class="text-end js-rating">
            @if (getUser() && getUser('id') !== $post->user_id)
                <a class="post-rating-down<?= ($polls[$post->getMorphClass()][$post->id] ?? '') === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->rating) }}</b>
            @if (getUser() && getUser('id') !== $post->user_id)
                <a class="post-rating-up<?=($polls[$post->getMorphClass()][$post->id] ?? '') === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    @if ($post->getImages()->isNotEmpty())
        @include('app/_viewer', ['model' => $post, 'files' => $post->getImages()])
    @endif

    <div class="section-message">
        {{ bbCode($post->text) }}
    </div>

    @if ($post->links || $post->files->isNotEmpty())
        @foreach ($post->getFiles() as $file)
            <div class="media-file mb-3">
                @if ($file->path && file_exists(public_path($file->path)))
                    @if ($file->isAudio())
                        <div>
                            <audio src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls controlsList="{{ $allowDownload ? null : 'nodownload' }}"></audio>
                        </div>
                    @endif

                    @if ($file->isVideo())
                        <div>
                            <video src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls playsinline controlsList="{{ $allowDownload ? null : 'nodownload' }}"></video>
                        </div>
                    @endif

                    {{ icons($file->extension) }}
                    <b>{{ $file->name }}</b> ({{ formatSize($file->size) }})<br>

                    @if ($allowDownload)
                        <a class="btn btn-sm btn-success" href="/downs/download/{{ $file->id }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a><br>
                    @endif
                @else
                    <i class="fa fa-download"></i> {{ __('main.file_not_found') }}
                @endif
            </div>
        @endforeach

        @if ($post->links && $allowDownload)
            @foreach ($post->links as $linkId => $link)
                <div class="media-file mb-3">
                    <b>{{ basename($link) }}</b><br>
                    <a class="btn btn-sm btn-success" href="/downs/download/{{ $post->id }}/{{ $linkId }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a><br>
                </div>
            @endforeach
        @endif

        @if (! $allowDownload)
            {{ showError(__('loads.download_authorized')) }}
        @endif
    @else
        {{ showError(__('main.not_uploaded')) }}
    @endif

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="/downs/comments/{{ $post->id }}">{{ __('main.comments') }}</a> ({{ $post->count_comments }})
    <a href="/downs/end/{{ $post->id }}">&raquo;</a>
</div>
