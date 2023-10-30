@forelse ($posts as $post)
    {{-- Посты --}}
    @if ($post instanceof \App\Models\Topic)
        <div class="section mb-3 shadow">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item">
                    <i class="fa-regular fa-message"></i> <a href="/forums" class="text-muted"> {{ __('index.forums') }}</a>
                </li>
                @if ($post->forum->parent->id)
                    <li class="breadcrumb-item">
                        <a href="/forums/{{ $post->forum->parent->id }}" class="text-muted">{{ $post->forum->parent->title }}</a>
                    </li>
                @endif

                <li class="breadcrumb-item">
                    <a href="/forums/{{ $post->forum->id }}" class="text-muted">{{ $post->forum->title }}</a>
                </li>
            </ol>

            <div class="section-header d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="section-title">
                        <h3><a class="post-title" href="/topics/{{ $post->id }}">{{ $post->title }}</a></h3>
                    </div>
                </div>

                <div class="js-rating text-end">
                    @if ($user && $user->id !== $post->lastPost->user_id)
                        <a class="post-rating-down{{ ($polls[$post->lastPost::$morphName][$post->lastPost->id] ?? '') === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->lastPost->id }}" data-type="{{ $post->lastPost->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-down"></i></a>
                    @endif
                    <b>{{ formatNum($post->lastPost->rating) }}</b>
                    @if ($user && $user->id !== $post->lastPost->user_id)
                        <a class="post-rating-up{{ ($polls[$post->lastPost::$morphName][$post->lastPost->id] ?? '') === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->lastPost->id }}" data-type="{{ $post->lastPost->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-up"></i></a>
                    @endif
                </div>
            </div>

            <div class="section-content">
                <div class="section-message">
                    {{ $post->lastPost->text ? bbCode($post->lastPost->text) : 'Удалено' }}
                </div>

                @if ($post->lastPost->files->isNotEmpty())
                    @foreach ($post->lastPost->files as $file)
                        <div class="media-file">
                            @if ($file->isImage())
                                <a href="{{ $file->hash }}" data-fancybox="{{ $post->lastPost->id }}">{{ resizeImage($file->hash, ['alt' => $file->name]) }}</a><br>
                            @endif

                            @if ($file->isAudio())
                                <div>
                                    <audio src="{{ $file->hash }}" style="max-width:100%;" preload="metadata" controls></audio>
                                </div>
                            @endif
                            {{ icons($file->extension) }}
                            <a href="{{ $file->hash }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="section-body">
                <span class="avatar-micro">{{ $post->lastPost->user->getAvatarImage() }}</span> {{ $post->lastPost->user->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($post->lastPost->created_at) }}</small>
            </div>

            <i class="fa-regular fa-comment"></i> <a href="/topics/{{ $post->id }}">{{ __('main.messages') }}</a> ({{ $post->count_posts }})
            <a href="/topics/end/{{ $post->id }}">&raquo;</a>
        </div>
    @endif

    {{-- Новости --}}
    @if ($post instanceof \App\Models\News)
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
                            <a href="{{ $post->image }}" data-fancybox>{{ resizeImage($post->image, ['class' => 'img-thumbnail img-fluid', 'alt' => $post->title]) }}</a>
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
    @endif

    {{-- Галерея --}}
    @if ($post instanceof \App\Models\Photo)
        <div class="section mb-3 shadow">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item">
                    <i class="fa-regular fa-image"></i> <a href="/photos" class="text-muted">{{ __('index.photos') }}</a>
                </li>
            </ol>

            <div class="section-header d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="section-title">
                        <h3><a class="post-title" href="/photos/{{ $post->id }}">{{ $post->title }}</a></h3>
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
                @include('app/_carousel', ['model' => $post])

                @if ($post->text)
                    {{ bbCode($post->text) }}<br>
                @endif
            </div>

            <div class="section-body">
                <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
            </div>

            <i class="fa-regular fa-comment"></i> <a href="/photos/comments/{{ $post->id }}">{{ __('main.comments') }}</a> ({{ $post->count_comments }})
            <a href="/photos/end/{{ $post->id }}">&raquo;</a>
        </div>
    @endif

    {{-- Статьи --}}
    @if ($post instanceof \App\Models\Article)
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
                {{ $post->shortText() }}
            </div>

            <div class="section-body">
                <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
            </div>

            <i class="fa-regular fa-comment"></i> <a href="/articles/comments/{{ $post->id }}">{{ __('main.comments') }}</a> ({{ $post->count_comments }})
            <a href="/articles/end/{{ $post->id }}">&raquo;</a>
        </div>
    @endif

    {{-- Загрузки --}}
    @if ($post instanceof \App\Models\Down)
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
                        <a class="post-rating-down<?= ($polls[$post::$morphName][$post->id] ?? '') === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                    @endif
                    <b>{{ formatNum($post->rating) }}</b>
                    @if (getUser() && getUser('id') !== $post->user_id)
                        <a class="post-rating-up<?=($polls[$post::$morphName][$post->id] ?? '') === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                    @endif
                </div>
            </div>

            @if ($post->getImages()->isNotEmpty())
                @include('app/_carousel', ['model' => $post, 'files' => $post->getImages()])
            @endif

            <div class="section-message mb-3">
                {{ bbCode($post->text) }}
            </div>

            @if ($post->links || $post->files->isNotEmpty())
                @foreach ($post->getFiles() as $file)
                    <div class="media-file mb-3">
                        @if ($file->hash && file_exists(public_path($file->hash)))
                            @if ($file->extension === 'mp3')
                                <div>
                                    <audio src="{{ $file->hash }}" style="max-width:100%;" preload="metadata" controls controlsList="{{ $allowDownload ? null : 'nodownload' }}"></audio>
                                </div>
                            @endif

                            @if ($file->extension === 'mp4')
                                <div>
                                    <video src="{{ $file->hash }}" style="max-width:100%;" preload="metadata" controls playsinline controlsList="{{ $allowDownload ? null : 'nodownload' }}"></video>
                                </div>
                            @endif

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
    @endif

    {{-- Объявления --}}
    @if ($post instanceof \App\Models\Item)
        <div class="section mb-3 shadow">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item">
                    <i class="fa-solid fa-rectangle-list"></i> <a href="/boards" class="text-muted">{{ __('index.boards') }}</a>
                </li>

                @if ($post->category->parent->id)
                    <li class="breadcrumb-item">
                        <a href="/boards/{{ $post->category->parent->id }}" class="text-muted">{{ $post->category->parent->name }}</a>
                    </li>
                @endif

                <li class="breadcrumb-item">
                    <a href="/boards/{{ $post->category->id }}" class="text-muted">{{ $post->category->name }}</a>
                </li>
            </ol>

            <h3><a class="post-title" href="/items/{{ $post->id }}">{{ $post->title }}</a></h3>

            <div class="col-md-12">
                @if ($post->files->isNotEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            @include('app/_carousel', ['model' => $post])
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-10">
                        <div class="section-message mb-3">
                            {{ bbCode($post->text) }}
                        </div>
                        <div>
                            @if ($post->phone)
                                <span class="badge rounded-pill bg-primary mb-3">{{ __('boards.phone') }}: {{ $post->phone }}</span><br>
                            @endif

                            <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
                            <small class="section-date text-muted fst-italic">{{ dateFixed($post->updated_at) }}</small>
                        </div>
                    </div>

                    <div class="col-md-2">
                        @if ($post->price)
                            <button type="button" class="btn btn-sm btn-light">{{ $post->price }} {{ setting('currency') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@empty
    {{ showError(__('forums.empty_posts')) }}
@endforelse

<div class="d-flex justify-content-center">
    {{ $posts->links() }}
</div>
