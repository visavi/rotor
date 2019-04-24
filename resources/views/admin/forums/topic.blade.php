@extends('layout')

@section('title')
    {{ $topic->title }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('description', trans('forums.topic_discussion') . ': ' .$topic->title)

@section('header')
    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ trans('forums.forum') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
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

    @if ($topic->closed)
        <a href="/admin/topics/action/{{ $topic->id }}?type=open&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.open') }}</a> /
    @else
        <a href="/admin/topics/action/{{ $topic->id }}?type=closed&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}"  onclick="return confirm('{{ trans('forums.confirm_close_topic') }}')">{{ trans('main.close') }}</a> /
    @endif

    @if ($topic->locked)
        <a href="/admin/topics/action/{{ $topic->id }}?type=unlocked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.unlock') }}</a> /
    @else
        <a href="/admin/topics/action/{{ $topic->id }}?type=locked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.lock') }}</a> /
    @endif

    <a href="/admin/topics/edit/{{ $topic->id }}">{{ trans('main.change') }}</a> /
    <a href="/admin/topics/move/{{ $topic->id }}">{{ trans('main.move') }}</a> /
    <a href="/admin/topics/delete/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('forums.confirm_delete_topic') }}')">{{ trans('main.delete') }}</a> /
    <a href="/topics/{{ $topic->id }}?page={{ $page->current }}">{{ trans('main.review') }}</a><br>


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
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                @foreach ($vote->answers as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                @endforeach
                <br><button class="btn btn-sm btn-primary">{{ trans('forums.vote') }}</button>
            </form><br>
        @endif

        {{ trans('forums.total_votes') }}: {{ $vote->count }}
    @endif

    <form action="/admin/posts/delete?tid={{ $topic->id }}&amp;page={{ $page->current }}" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="p-1 bg-light text-right">
            <label for="all">{{ trans('main.select_all') }}</label>
            <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
        </div>

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

                                <a href="/admin/posts/edit/{{ $data->id }}?page={{ $page->current }}" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>

                                <input type="checkbox" name="del[]" value="{{ $data->id }}">
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

        <span class="float-right">
            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </span>
    </form>

    {!! pagination($page) !!}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="form">
                <form action="/topics/create/{{ $topic->id }}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

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
    <a href="/forums/top/posts">{{ trans('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $topic->forum_id }}">{{ trans('main.search') }}</a><br>
@stop
