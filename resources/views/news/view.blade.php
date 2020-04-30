@extends('layout')

@section('title')
    {{ $news->title }}
@stop

@section('description', truncateDescription(bbCode($news->text, false)))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ $news->title }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/news/edit/{{ $news->id }}">{{ __('main.edit') }}</a></li>
                <li class="breadcrumb-item"><a href="/admin/news/delete/{{ $news->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('news.confirm_delete') }}')">{{ __('main.delete') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->image)
        <div class="img">
            <a href="{{ $news->image }}" class="gallery">{!! resizeImage($news->image, ['width' => 100, 'alt' => $news->title]) !!}</a>
        </div>
    @endif

    <div>{!! bbCode($news->text) !!}</div>

    <div style="clear:both;">
        {{ __('main.added') }}: {!! $news->user->getProfile() !!} ({{ dateFixed($news->created_at) }})

        <div class="js-rating">{{ __('main.rating') }}:
            @if (getUser() && getUser('id') !== $news->user_id)
                <a class="post-rating-down<?= $news->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
            @endif
            <span>{!! formatNum($news->rating) !!}</span>
            @if (getUser() && getUser('id') !== $news->user_id)
                <a class="post-rating-up<?= $news->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
            @endif
        </div>
    </div><br>

    @if ($comments->isNotEmpty())
        <div class="b"><i class="fa fa-comment"></i> <b>{{ __('main.last_comments') }}</b></div>

        @foreach ($comments as $comment)
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! $comment->user->getAvatar() !!}
                        {!! $comment->user->getOnline() !!}
                    </div>

                    <b>{!! $comment->user->getProfile() !!}</b>
                    <small> ({{ dateFixed($comment->created_at) }})</small><br>
                    {!! $comment->user->getStatus() !!}
                </div>

                <div>
                    {!! bbCode($comment->text) !!}<br>

                    @if (isAdmin())
                     <span class="data">({{ $comment->brow }}, {{ $comment->ip }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="bg-light p-1 mb-3 border">
            <i class="fas fa-comments"></i> <b><a href="/news/comments/{{ $news->id }}">{{ __('news.all_comments') }}</a></b> ({{ $news->count_comments }})
            <a href="/news/end/{{ $news->id }}">&raquo;</a>
        </div>
    @endif

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {!! showError(__('main.empty_comments')) !!}
        @endif

        @if (getUser())
            <div class="section-form p-2 shadow">
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
            {!! showError(__('main.not_authorized')) !!}
        @endif
    @else
        {!! showError(__('news.closed_news')) !!}
    @endif
@stop
