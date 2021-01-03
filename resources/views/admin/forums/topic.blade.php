@extends('layout')

@section('title', $topic->title . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('header')
    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ __('index.forums') }}</a></li>

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
                <i class="fa fa-wrench"></i> {{ __('forums.topic_curators') }}:
                @foreach ($topic->curators as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{!! $curator->getProfile() !!}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="p-1 my-1 bg-info text-white">{!! bbCode($topic->note) !!}</div>
    @endif

    <hr>

    @if ($topic->closed)
        <a href="/admin/topics/action/{{ $topic->id }}?type=open&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.open') }}</a> /
    @else
        <a href="/admin/topics/action/{{ $topic->id }}?type=closed&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}"  onclick="return confirm('{{ __('forums.confirm_close_topic') }}')">{{ __('main.close') }}</a> /
    @endif

    @if ($topic->locked)
        <a href="/admin/topics/action/{{ $topic->id }}?type=unlocked&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.unlock') }}</a> /
    @else
        <a href="/admin/topics/action/{{ $topic->id }}?type=locked&amp;page={{ $posts->currentPage() }}&amp;token={{ $_SESSION['token'] }}">{{ __('main.lock') }}</a> /
    @endif

    <a href="/admin/topics/edit/{{ $topic->id }}">{{ __('main.change') }}</a> /
    <a href="/admin/topics/move/{{ $topic->id }}">{{ __('main.move') }}</a> /
    <a href="/admin/topics/delete/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')">{{ __('main.delete') }}</a> /
    <a href="/topics/{{ $topic->id }}?page={{ $posts->currentPage() }}">{{ __('main.review') }}</a><br>

    @if ($vote)
        <h3>{{ $vote->title }}</h3>

        @if ($vote->poll || $vote->closed || ! getUser())
            @foreach ($vote->voted as $key => $data)
                <?php $proc = round(($data * 100) / $vote->sum, 1); ?>
                <?php $maxproc = round(($data * 100) / $vote->max); ?>

                <b>{{ $key }}</b> ({{ __('forums.votes') }}: {{ $data }})<br>
                {!! progressBar($maxproc, $proc . '%') !!}
            @endforeach
        @else
            <form action="/topics/votes/{{ $topic->id }}?page={{ $posts->currentPage() }}" method="post">
                @csrf
                @foreach ($vote->answers as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                @endforeach
                <br><button class="btn btn-sm btn-primary">{{ __('forums.vote') }}</button>
            </form><br>
        @endif

        {{ __('forums.total_votes') }}: {{ $vote->count }}
    @endif

    <form action="/admin/posts/delete?tid={{ $topic->id }}&amp;page={{ $posts->currentPage() }}" method="post">
        @csrf
        <div class="p-1 bg-light text-right">
            <label for="all">{{ __('main.select_all') }}</label>
            <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
        </div>

        @if ($posts->isNotEmpty())
            @foreach ($posts as $data)
                <div class="post">
                    <div class="b" id="post_{{ $data->id }}">
                        <div class="float-right text-right">
                            @if (getUser())
                                @if (getUser('id') !== $data->user_id)
                                    <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                    <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                    <a href="#" onclick="return sendComplaint(this)" data-type="{{ $data->getMorphClass() }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $posts->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                                @endif

                                <a href="/admin/posts/edit/{{ $data->id }}?page={{ $posts->currentPage() }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>

                                <input type="checkbox" name="del[]" value="{{ $data->id }}">
                            @endif

                            <div class="js-rating">
                                @if (getUser() && getUser('id') !== $data->user_id)
                                    <a class="post-rating-down{{ $data->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-minus"></i></a>
                                @endif
                                <b>{!! formatNum($data->rating) !!}</b>
                                @if (getUser() && getUser('id') !== $data->user_id)
                                    <a class="post-rating-up{{ $data->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-plus"></i></a>
                                @endif
                            </div>
                        </div>

                        <div class="img">
                            {!! $data->user->getAvatar() !!}
                            {!! $data->user->getOnline() !!}
                        </div>

                        {!! $data->user->getProfile() !!} <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! $data->user->getStatus() !!}
                    </div>

                    <div class="section-message">
                        {!! bbCode($data->text) !!}
                    </div>

                    @if ($data->files->isNotEmpty())
                        <div class="hidden-text">
                            <i class="fa fa-paperclip"></i> <b>{{ __('main.attached_files') }}:</b><br>
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
                        <small><i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $data->editUser->getName() }} ({{ dateFixed($data->updated_at) }})</small><br>
                    @endif

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">
                            {{ $data->brow }}, {{ $data->ip }}
                        </div>
                    @endif
                </div>
            @endforeach

        @else
            {!! showError(__('forums.empty_posts')) !!}
        @endif

        <span class="float-right">
            <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
        </span>
    </form>

    {{ $posts->links() }}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="section-form shadow">
                <form action="/topics/create/{{ $topic->id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ __('forums.post') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" placeholder="{{ __('forums.post') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    @if (getUser('point') >= setting('forumloadpoints'))
                        <div class="js-attach-form" style="display: none;">
                            <div class="custom-file{{ hasError('files') }}">
                                <label class="btn btn-sm btn-secondary" for="files">
                                    <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ __('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                                    {{ __('main.attach_files') }}&hellip;
                                </label>
                                <span class="badge badge-info" id="upload-file-info"></span>
                                <div class="invalid-feedback">{{ textError('files') }}</div>
                            </div>

                            <p class="text-muted font-italic">
                                {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
                                {{ __('main.max_file_weight') }}: {{ formatSize(setting('forumloadsize')) }}<br>
                                {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('forumextload')) }}
                            </p>
                        </div>

                        <span class="float-right js-attach-button">
                            <a href="#" onclick="return showAttachForm();">{{ __('main.attach_files') }}</a>
                        </span>
                    @endif

                    <button class="btn btn-primary">{{ __('main.write') }}</button>
                </form>
            </div>

        @else
            {!! showError(__('forums.topic_closed')) !!}
        @endif
    @else
        {!! showError(__('main.not_authorized')) !!}
    @endif

    <a href="/stickers">{{ __('main.stickers') }}</a>  /
    <a href="/tags">{{ __('main.tags') }}</a>  /
    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $topic->forum_id }}">{{ __('main.search') }}</a><br>
@stop
