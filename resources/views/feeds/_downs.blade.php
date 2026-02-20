<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-download"></i> <a href="{{ route('loads.index') }}" class="text-muted">{{ __('index.loads') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="{{ route('loads.load', ['id' => $post->category->parent->id]) }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

        <li class="breadcrumb-item">
            <a href="{{ route('loads.load', ['id' => $post->category_id]) }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('downs.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>

        <div class="text-end section-action js-rating">
            @if (getUser() && getUser('id') !== $post->user_id)
                <a class="post-rating-down<?= ($polls[$post->getMorphClass()][$post->id] ?? '') === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-"><i class="fa fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($post->rating) }}</b>
            @if (getUser() && getUser('id') !== $post->user_id)
                <a class="post-rating-up<?=($polls[$post->getMorphClass()][$post->id] ?? '') === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+"><i class="fa fa-arrow-up"></i></a>
            @endif
        </div>
    </div>

    <div class="section-content short-view">
        @if ($post->getImages()->isNotEmpty())
            @include('app/_image_viewer', ['model' => $post, 'files' => $post->getImages()])
        @endif

        <div class="section-message">
            {{ $post->getText() }}
        </div>

        @if ($post->links || $post->files->isNotEmpty())
            @foreach ($post->getFiles() as $file)
                <div class="media-file">
                    @if ($file->path && file_exists(public_path($file->path)))
                        @if ($file->isAudio())
                            <div>
                                <audio src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls controlsList="{{ $allowDownload ? null : 'nodownload' }}"></audio>
                            </div>
                        @endif

                        @if ($file->isVideo())
                            <div>
                                <video src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls playsinline controlsList="{{ $allowDownload ? null : 'nodownload' }}"></video>
                            </div>
                        @endif

                        {{ icons($file->extension) }}
                        <b>{{ $file->name }}</b> ({{ formatSize($file->size) }})<br>

                        @if ($allowDownload)
                            <a class="btn btn-sm btn-success" href="{{ route('downs.download', ['id' => $post->id, 'fid' => $file->id]) }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a><br>
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
                        <a class="btn btn-sm btn-success" href="{{ route('downs.download-link', ['id' => $post->id, 'lid' => $linkId]) }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a><br>
                    </div>
                @endforeach
            @endif

            @if (! $allowDownload)
                {{ showError(__('loads.download_authorized')) }}
            @endif
        @else
            {{ showError(__('main.not_uploaded')) }}
        @endif
    </div>

    <div class="section-body">
        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>

    <i class="fa-regular fa-comment"></i> <a href="{{ route('downs.comments', ['id' => $post->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $post->count_comments }}</span>
</div>
