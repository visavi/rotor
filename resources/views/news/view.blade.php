@extends('layout')

@section('title', $news->title)

@section('description', truncateDescription(bbCode($news->text, false)))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ $news->title }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if (isAdmin())
        <div class="float-right">
            <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-wrench"></i>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/admin/news/edit/{{ $news->id }}">{{ __('main.edit') }}</a>
                    <a class="dropdown-item" href="/admin/news/delete/{{ $news->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('news.confirm_delete') }}')">{{ __('main.delete') }}</a>
                </div>
            </div>
        </div>
    @endif

    <h1>{{ $news->title }}</h1>
@stop

@section('content')
    <div class="mb-3">
        <div class="section-content">
            <div class="section-message row mb-3">
                @if ($news->image)
                    <div class="col-sm-3 mb-3">
                        <a href="{{ $news->image }}" class="gallery">{{ resizeImage($news->image, ['class' => 'img-thumbnail img-fluid', 'alt' => $news->title]) }}</a>
                    </div>
                @endif

                <div class="col">
                    {{ bbCode($news->text) }}
                </div>
            </div>
        </div>

        <div class="section-body">
            {{ __('main.added') }}: {{ $news->user->getProfile() }} <small class="section-date text-muted font-italic">{{ dateFixed($news->created_at) }}</small>

            <div class="js-rating">
                {{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $news->user_id)
                    <a class="post-rating-down<?= $news->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
                @endif
                <b>{{ formatNum($news->rating) }}</b>
                @if (getUser() && getUser('id') !== $news->user_id)
                    <a class="post-rating-up<?= $news->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
                @endif
            </div>
        </div>
    </div>

    @if ($comments->isNotEmpty())
        <h5><i class="fa fa-comment"></i> {{ __('main.last_comments') }}</h5>

        @foreach ($comments as $comment)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $comment->user->getAvatar() }}
                    {{ $comment->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $comment->user->getProfile() }}

                        <small class="section-date text-muted font-italic">{{ dateFixed($comment->created_at) }}</small><br>
                        <small class="font-italic">{{ $comment->user->getStatus() }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($comment->text) }}
                    </div>

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">{{ $comment->brow }}, {{ $comment->ip }}</div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="p-3 mb-3 shadow">
            <i class="fas fa-comments"></i> <b><a href="/news/comments/{{ $news->id }}">{{ __('news.all_comments') }}</a></b> ({{ $news->count_comments }})
            <a href="/news/end/{{ $news->id }}">&raquo;</a>
        </div>
    @endif

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {{ showError(__('main.empty_comments')) }}
        @endif

        @if (getUser())
            <div class="section-form mb-3 shadow">
                <form action="/news/comments/{{ $news->id }}?read=1" method="post">
                    @csrf
                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ __('main.message') }}:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_length') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <button class="btn btn-success">{{ __('main.write') }}</button>
                </form>
            </div>

            <a href="/rules">{{ __('main.rules') }}</a> /
            <a href="/stickers">{{ __('main.stickers') }}</a> /
            <a href="/tags">{{ __('main.tags') }}</a><br><br>
        @else
            {{ showError(__('main.not_authorized')) }}
        @endif
    @else
        {{ showError(__('news.closed_news')) }}
    @endif
@stop
