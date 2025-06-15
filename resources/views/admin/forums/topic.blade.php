@extends('layout')

@section('title', $topic->title . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('header')
    <div class="btn-group float-end">
        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-wrench"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            @if ($topic->closed)
                <a class="dropdown-item" href="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'open', 'page' => $posts->currentPage(), '_token' => csrf_token()]) }}">{{ __('main.open') }}</a>
            @else
                <a class="dropdown-item" href="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'closed', 'page' => $posts->currentPage(), '_token' => csrf_token()]) }}"  onclick="return confirm('{{ __('forums.confirm_close_topic') }}')">{{ __('main.close') }}</a>
            @endif

            @if ($topic->locked)
                <a class="dropdown-item" href="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'unlocked', 'page' => $posts->currentPage(), '_token' => csrf_token()]) }}">{{ __('main.unlock') }}</a>
            @else
                <a class="dropdown-item" href="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'locked', 'page' => $posts->currentPage(), '_token' => csrf_token()]) }}">{{ __('main.lock') }}</a>
            @endif

            <a class="dropdown-item" href="{{ route('admin.topics.edit', ['id' => $topic->id]) }}">{{ __('main.change') }}</a>
            <a class="dropdown-item" href="{{ route('admin.topics.move', ['id' => $topic->id]) }}">{{ __('main.move') }}</a>
            <a class="dropdown-item" href="{{ route('admin.topics.delete', ['id' => $topic->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')">{{ __('main.delete') }}</a>
            <a class="dropdown-item" href="{{ route('topics.topic', ['id' => $topic->id, 'page' => $posts->currentPage()]) }}">{{ __('main.review') }}</a>
        </div>
    </div>

    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.forums.index') }}">{{ __('index.forums') }}</a></li>

            @foreach ($topic->forum->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.forums.forum', ['id' => $parent->id ]) }}">{{ $parent->title }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topic->curators)
       <div>
            <span class="badge bg-adaptive">
                <i class="fa fa-wrench"></i> {{ __('forums.topic_curators') }}:
                @foreach ($topic->curators as $key => $curator)
                    @php
                        $comma = (empty($key)) ? '' : ', ';
                    @endphp
                    {{ $comma }}{{ $curator->getProfile() }}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="section-form my-1">{{ bbCode($topic->note) }}</div>
    @endif

    @if ($vote)
        <h5>{{ $vote->title }}</h5>

        <div class="mb-3">
            @if ($vote->poll || $vote->closed)
                @foreach ($vote->voted as $key => $data)
                    @php
                        $proc = round(($data * 100) / $vote->sum, 1);
                        $maxproc = round(($data * 100) / $vote->max);
                    @endphp

                    <b>{{ $key }}</b> ({{ __('forums.votes') }}: {{ $data }})<br>
                    {{ progressBar($maxproc, $proc . '%') }}
                @endforeach
            @else
                <form class="mb-3" action="{{ route('topics.vote', ['id' => $topic->id, 'page' => $posts->currentPage()]) }}" method="post">
                    @csrf
                    @foreach ($vote->answers as $answer)
                        <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                    @endforeach
                    <button class="btn btn-sm btn-primary mt-3">{{ __('forums.vote') }}</button>
                </form>
            @endif

            {{ __('forums.total_votes') }}: {{ $vote->count }}
        </div>
    @endif

    <form action="{{ route('admin.posts.delete', ['tid' => $topic->id, 'page' => $posts->currentPage()]) }}" method="post">
        @csrf

        <div class="section-form py-1 my-2 text-end">
            <label for="all" class="form-label">{{ __('main.select_all') }}</label>
            <input type="checkbox" class="form-check-input" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
        </div>

        @if ($posts->isNotEmpty())
            @foreach ($posts as $data)
                <div class="section mb-3 shadow" id="post_{{ $data->id }}">
                    <div class="user-avatar">
                        {{ $data->user->getAvatar() }}
                        {{ $data->user->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {{ $data->user->getProfile() }}
                            <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }})</small><br>
                            <small class="fst-italic">{{ $data->user->getStatus() }}</small>
                        </div>

                        <div class="text-end">
                            @if (getUser())
                                <div class="section-action">
                                @if (getUser('id') !== $data->user_id)
                                    <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                    <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>
                                @endif

                                <a href="{{ route('admin.posts.edit', ['id' => $data->id, 'page' => $posts->currentPage()]) }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>

                                <input type="checkbox" class="form-check-input" name="del[]" value="{{ $data->id }}">
                                </div>
                            @endif

                            <div class="section-action js-rating">
                                @if (getUser() && getUser('id') !== $data->user_id)
                                    <a class="post-rating-down{{ $data->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-minus"></i></a>
                                @endif
                                <b>{{ formatNum($data->rating) }}</b>
                                @if (getUser() && getUser('id') !== $data->user_id)
                                    <a class="post-rating-up{{ $data->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-plus"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="section-body border-top">
                        <div class="section-message">
                            {{ bbCode($data->text) }}
                        </div>

                        @include('app/_media_viewer', ['model' => $data])

                        @if ($data->edit_user_id)
                            <div class="small">
                                <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $data->editUser->getName() }} <small class="section-date text-muted fst-italic">{{ dateFixed($data->updated_at) }}</small>
                            </div>
                        @endif

                        <div class="small text-muted fst-italic mt-2">
                            {{ $data->brow }}, {{ $data->ip }}
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        @else
            {{ showError(__('forums.empty_posts')) }}
        @endif
    </form>

    {{ $posts->links() }}

    @if (empty($topic->closed))
        <div class="section-form mb-3 shadow">
            <form action="{{ route('topics.create', ['id' => $topic->id]) }}" method="post">
                @csrf
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('forums.post') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('forum_text_max') }}" id="msg" rows="5" name="msg" placeholder="{{ __('forums.post') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                @include('app/_upload_file', [
                    'model' => App\Models\Post::getModel(),
                    'files' => $files,
                ])

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>

    @else
        {{ showError(trans_choice('forums.topic_closed_user', $topic->closeUser->id, ['login' => $topic->closeUser->getName()])) }}
    @endif

    <a href="/stickers">{{ __('main.stickers') }}</a>  /
    <a href="/tags">{{ __('main.tags') }}</a>  /
    <a href="/rules">{{ __('main.rules') }}</a><br>
@stop
