@extends('layout')

@section('title', $topic->title . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('description', $description)

@section('header')
    @if (isAdmin())
        <div class="btn-group float-end">
            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-wrench"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                @if ($topic->closed)
                    <a class="dropdown-item" href="/admin/topics/action/{{ $topic->id }}?type=open&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.open') }}</a>
                @else
                    <a class="dropdown-item" href="/admin/topics/action/{{ $topic->id }}?type=closed&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.close') }}</a>
                @endif

                @if ($topic->locked)
                    <a class="dropdown-item" href="/admin/topics/action/{{ $topic->id }}?type=unlocked&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.unlock') }}</a>
                @else
                    <a class="dropdown-item" href="/admin/topics/action/{{ $topic->id }}?type=locked&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.lock') }}</a>
                @endif

                <a class="dropdown-item" href="/admin/topics/edit/{{ $topic->id }}">{{ __('main.change') }}</a>
                <a class="dropdown-item" href="/admin/topics/move/{{ $topic->id }}">{{ __('main.move') }}</a>
                <a class="dropdown-item" href="/admin/topics/delete/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')">{{ __('main.delete') }}</a>
                <a class="dropdown-item" href="/admin/topics/{{ $topic->id }}?page={{ $posts->currentPage() }}">{{ __('main.management') }}</a>
            </div>
        </div>
    @endif

    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fas fa-print"></i> <a class="me-3" href="/topics/print/{{ $topic->id }}">{{ __('main.print') }}</a>
    <i class="fas fa-rss"></i> <a class="me-3" href="/topics/rss/{{ $topic->id }}">{{ __('main.rss') }}</a>

    @if (getUser())
        @if (! $topic->closed && getUser('id') === $topic->user->id && getUser('point') >= setting('editforumpoint'))
            <i class="fas fa-lock"></i> <a class="me-3" href="/topics/close/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('forums.confirm_close_topic') }}')">{{ __('main.close') }}</a>
            <i class="fas fa-pencil-alt"></i> <a class="me-3" href="/topics/edit/{{ $topic->id }}">{{ __('main.edit') }}</a>
        @endif

        @if ($topic->closed && getUser('id') === $topic->closeUser->id)
            <i class="fas fa-unlock"></i> <a class="me-3" href="/topics/open/{{ $topic->id }}?token={{ $_SESSION['token'] }}">{{ __('main.open') }}</a>
        @endif

        <?php $bookmark = $topic->bookmark_posts ? __('forums.from_bookmarks') : __('forums.to_bookmarks'); ?>
        <i class="fas fa-bookmark"></i> <a class="me-3" href="#" onclick="return bookmark(this)" data-tid="{{ $topic->id }}" data-token="{{ $_SESSION['token'] }}" data-from="{{ __('forums.from_bookmarks') }}"  data-to="{{ __('forums.to_bookmarks') }}">{{ $bookmark }}</a>
    @endif

    <div class="float-end" data-bs-toggle="tooltip" title="{{ __('main.views') }}">
        <i class="far fa-eye"></i> {{ $topic->visits }}
    </div>

    @if ($topic->curators)
       <div>
            <span class="badge bg-warning">
                <i class="fa fa-wrench"></i> {{ __('forums.topic_curators') }}:
                @foreach ($topic->curators as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{{ $curator->getProfile() }}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="bg-info text-white p-1 my-1">{{ bbCode($topic->note) }}</div>
    @endif
    <hr>

    @if ($vote)
        <h5>{{ $vote->title }}</h5>

        <div class="mb-3">
            @if ($vote->poll || $vote->closed || ! getUser())
                @foreach ($vote->voted as $key => $value)
                    <?php $proc = round(($value * 100) / $vote->sum, 1); ?>
                    <?php $maxproc = round(($value * 100) / $vote->max); ?>

                    <b>{{ $key }}</b> ({{ __('forums.votes') }}: {{ $value }})<br>
                    {{ progressBar($maxproc, $proc . '%') }}
                @endforeach
            @else
                <form class="mb-3" action="/topics/votes/{{ $topic->id }}?page={{ $posts->currentPage() }}" method="post">
                    @csrf
                    @foreach ($vote->answers as $answer)
                        <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                    @endforeach
                    <button class="btn btn-sm btn-primary">{{ __('forums.vote') }}</button>
                </form>
            @endif

            {{ __('forums.total_votes') }}: {{ $vote->count }}
        </div>
    @endif

    @if ($topic->isModer)
        <form action="/topics/delete/{{ $topic->id }}?page={{ $posts->currentPage() }}" method="post">
            @csrf
    @endif

    @if ($posts->isNotEmpty())
        @foreach ($posts as $post)
            <div class="section mb-3 shadow" id="post_{{ $post->id }}">
                <div class="user-avatar">
                    {{ $post->user->getAvatar() }}
                    {{ $post->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $post->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>
                        <small class="fst-italic">{{ $post->user->getStatus() }}</small>
                    </div>

                    <div class="text-end">
                        @if (getUser())
                            @if (getUser('id') !== $post->user_id)
                                <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ $post->getMorphClass() }}" data-id="{{ $post->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $posts->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if ($topic->isModer || (getUser('id') === $post->user_id && $post->created_at + 600 > SITETIME))
                                <a href="/posts/edit/{{ $post->id }}?page={{ $posts->currentPage() }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                                @if ($topic->isModer)
                                    <input type="checkbox" name="del[]" value="{{ $post->id }}">
                                @endif
                            @endif
                        @endif

                        <div class="js-rating">
                            @if (getUser() && getUser('id') !== $post->user_id)
                                <a class="post-rating-down{{ $post->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-arrow-down"></i></a>
                            @endif
                            <b>{{ formatNum($post->rating) }}</b>
                            @if (getUser() && getUser('id') !== $post->user_id)
                                <a class="post-rating-up{{ $post->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-arrow-up"></i></a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($post->text) }}
                    </div>

                    @if ($post->files->isNotEmpty())
                        <div class="section-media">
                            <i class="fa fa-paperclip"></i> <b>{{ __('main.attached_files') }}:</b><br>
                            @foreach ($post->files as $file)
                                <div class="media-file">
                                    {{ icons($file->extension) }}
                                    <a href="{{ $file->hash }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                                    @if ($file->isImage())
                                        <a href="{{ $file->hash }}" class="gallery" data-group="{{ $post->id }}">{{ resizeImage($file->hash, ['alt' => $file->name]) }}</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($post->edit_user_id)
                        <div class="small">
                            <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $post->editUser->getName() }} ({{ dateFixed($post->updated_at) }})
                        </div>
                    @endif

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">{{ $post->brow }}, {{ $post->ip }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_posts')) }}
    @endif

    @if ($topic->isModer)
            <span class="float-end">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </span>
        </form>
    @endif

    {{ $posts->links() }}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="section-form mb-3 shadow">
                <form action="/topics/create/{{ $topic->id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3{{ hasError('msg') }}">
                        <label for="msg" class="form-label">{{ __('forums.post') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" placeholder="{{ __('forums.post') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <div class="js-attach-form" style="display: none;">
                        <div class="mb-3{{ hasError('files') }}">
                            <label for="files" class="btn btn-sm btn-secondary form-label">
                                <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ __('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                                {{ __('main.attach_files') }}&hellip;
                            </label>
                            <span class="badge bg-info" id="upload-file-info"></span>
                            <div class="invalid-feedback">{{ textError('files') }}</div>
                        </div>

                        <p class="text-muted fst-italic">
                            {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
                            {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
                            {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('file_extensions')) }}
                        </p>
                    </div>

                    <span class="float-end js-attach-button">
                        <a href="#" onclick="return showAttachForm();">{{ __('main.attach_files') }}</a>
                    </span>

                    <button class="btn btn-primary">{{ __('main.write') }}</button>
                </form>
            </div>
        @else
            {{ showError(choice('forums.topic_closed_user', $topic->closeUser->id, ['login' => $topic->closeUser->getName()])) }}
        @endif
    @else
        {{ showError(__('main.not_authorized')) }}
    @endif

    <a href="/stickers">{{ __('main.stickers') }}</a>  /
    <a href="/tags">{{ __('main.tags') }}</a>  /
    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $topic->forum_id }}">{{ __('main.search') }}</a><br>
@stop
