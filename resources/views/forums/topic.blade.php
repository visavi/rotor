@extends('layout')

@section('title')
    {{ $topic->title }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('description')
    {{ trans('forums.topic_discussion') }}: {{ $topic->title }}
@stop

@section('header')
    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/topics/print/{{ $topic->id }}">{{ trans('main.print') }}</a> / <a href="/topics/rss/{{ $topic->id }}">{{ trans('main.rss') }}</a>

    @if (getUser())
        @if (! $topic->closed && $topic->user->id === getUser('id') && getUser('point') >= setting('editforumpoint'))
           / <a href="/topics/close/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('forums.confirm_close_topic') }}')">{{ trans('main.close') }}</a>
           / <a href="/topics/edit/{{ $topic->id }}">{{ trans('main.edit') }}</a>
        @endif

        <?php $bookmark = $topic->bookmark_posts ? trans('forums.from_bookmarks') : trans('forums.to_bookmarks'); ?>
        / <a href="#" onclick="return bookmark(this)" data-tid="{{ $topic->id }}" data-token="{{ $_SESSION['token'] }}" data-from="{{ trans('forums.from_bookmarks') }}"  data-to="{{ trans('forums.to_bookmarks') }}">{{ $bookmark }}</a>
    @endif

    @if ($topic->curators)
       <div>
            <span class="badge badge-warning">
                <i class="fa fa-wrench"></i> {{ trans('forums.topic_curators') }}:
                @foreach ($topic->curators as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{!! $curator->getProfile() !!}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="p-1 bg-info text-white">{!! bbCode($topic->note) !!}</div>
    @endif

    <hr>

    @if (isAdmin())
        @if ($topic->closed)
            <a href="/admin/topics/action/{{ $topic->id }}?type=open&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.open') }}</a> /
        @else
            <a href="/admin/topics/action/{{ $topic->id }}?type=closed&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.close') }}</a> /
        @endif

        @if ($topic->locked)
            <a href="/admin/topics/action/{{ $topic->id }}?type=unlocked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.unlock') }}</a> /
        @else
            <a href="/admin/topics/action/{{ $topic->id }}?type=locked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.lock') }}</a> /
        @endif

        <a href="/admin/topics/edit/{{ $topic->id }}">{{ trans('main.change') }}</a> /
        <a href="/admin/topics/move/{{ $topic->id }}">{{ trans('main.move') }}</a> /
        <a href="/admin/topics/delete/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('forums.confirm_delete_topic') }}')">{{ trans('main.delete') }}</a> /
        <a href="/admin/topics/{{ $topic->id }}?page={{ $page->current }}">{{ trans('main.management') }}</a><br>
    @endif

    @if ($vote)
        <h3>{{ $vote->title }}</h3>

        @if ($vote->poll || $vote->closed || ! getUser())
            @foreach ($vote->voted as $key => $data)
                <?php $proc = round(($data * 100) / $vote->sum, 1); ?>
                <?php $maxproc = round(($data * 100) / $vote->max); ?>

                <b>{{ $key }}</b> ({{ trans('forums.votes') }}: {{ $data }})<br>
                {!! progressBar($maxproc, $proc.'%') !!}
            @endforeach
        @else
            <form action="/topics/votes/{{ $topic->id }}?page={{ $page->current }}" method="post">
                @csrf
                @foreach ($vote->answers as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                @endforeach
                <br><button class="btn btn-sm btn-primary">{{ trans('forums.vote') }}</button>
            </form><br>
        @endif

        {{ trans('forums.total_votes') }}: {{ $vote->count }}
    @endif

    @if ($topic->isModer)
        <form action="/topics/delete/{{ $topic->id }}?page={{ $page->current }}" method="post">
            @csrf
    @endif

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <?php $num = ($page->offset + $loop->iteration ); ?>
            <div class="post">
                <div class="b" id="post_{{ $data->id }}">
                    <div class="float-right text-right">
                        @if (getUser())
                            @if (getUser('id') !== $data->user_id)
                                <a href="#" onclick="return postReply(this)" title="{{ trans('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" title="{{ trans('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Post::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" title="{{ trans('main.complaint') }}"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if ($topic->isModer || (getUser('id') === $data->user_id && $data->created_at + 600 > SITETIME))
                                <a href="/posts/edit/{{ $data->id }}?page={{ $page->current }}" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                                @if ($topic->isModer)
                                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                                @endif
                            @endif
                        @endif

                        <div class="js-rating">
                            @if (getUser() && getUser('id') !== $data->user_id)
                                <a class="post-rating-down{{ $data->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ App\Models\Post::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-minus"></i></a>
                            @endif
                            <span>{!! formatNum($data->rating) !!}</span>
                            @if (getUser() && getUser('id') !== $data->user_id)
                                <a class="post-rating-up{{ $data->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ App\Models\Post::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-plus"></i></a>
                            @endif
                        </div>
                    </div>

                    <div class="img">
                        {!! $data->user->getAvatar() !!}
                        {!! $data->user->getOnline() !!}
                    </div>

                    {{ $num }}. <b>{!! $data->user->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! $data->user->getStatus() !!}
                </div>

                <div class="message">
                    {!! bbCode($data->text) !!}
                </div>

                @if ($data->files->isNotEmpty())
                    <div class="hiding">
                        <i class="fa fa-paperclip"></i> <b>{{ trans('main.attached_files') }}:</b><br>
                        @foreach ($data->files as $file)
                            <?php $ext = getExtension($file->hash); ?>

                            {!! icons($ext) !!}
                            <a href="{{ $file->hash }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                            @if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png']))
                                <a href="{{ $file->hash }}" class="gallery" data-group="{{ $data->id }}">{!! resizeImage($file->hash, ['alt' => $file->name]) !!}</a><br>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if ($data->edit_user_id)
                    <small><i class="fa fa-exclamation-circle text-danger"></i> {{ trans('main.changed') }}: {{ $data->editUser->getName() }} ({{ dateFixed($data->updated_at) }})</small><br>
                @endif

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

    @else
        {!! showError(trans('forums.empty_posts')) !!}
    @endif

    @if ($topic->isModer)
            <span class="float-right">
                <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
            </span>
        </form>
    @endif

    {!! pagination($page) !!}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="form">
                <form action="/topics/create/{{ $topic->id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ trans('forums.post') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" placeholder="{{ trans('forums.post') }}" required>{{ getInput('msg') }}</textarea>
                        <span class="js-textarea-counter"></span>
                        {!! textError('msg') !!}
                    </div>

                    @if (getUser('point') >= setting('forumloadpoints'))
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-secondary" for="files">
                                <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ trans('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                                {{ trans('main.attach_files') }}&hellip;
                            </label>
                            <span class="badge badge-info" id="upload-file-info"></span>
                            {!! textError('files') !!}
                            <br>

                            <p class="text-muted font-italic">
                                {{ trans('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
                                {{ trans('main.max_file_weight') }}: {{ formatSize(setting('forumloadsize')) }}<br>
                                {{ trans('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('forumextload')) }}
                            </p>
                        </div>

                        <span class="float-right js-attach-button">
                            <a href="#" onclick="return showAttachForm();">{{ trans('main.attach_files') }}</a>
                        </span>
                    @endif

                    <button class="btn btn-primary">{{ trans('main.write') }}</button>
                </form>
            </div><br>

        @else
            {!! showError(trans('forums.topic_closed')) !!}
        @endif
    @else
        {!! showError(trans('main.not_authorized')) !!}
    @endif

    <a href="/stickers">{{ trans('main.stickers') }}</a>  /
    <a href="/tags">{{ trans('main.tags') }}</a>  /
    <a href="/rules">{{ trans('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ trans('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ trans('forums.top_topics') }}</a> /
    <a href="/forums/search?fid={{ $topic->forum_id }}">{{ trans('main.search') }}</a><br>
@stop
