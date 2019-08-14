@extends('layout')

@section('title')
    {{ $news->title }}
@stop

@section('description', truncateDescription(bbCode($news->text, false)))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ $news->title }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/news/edit/{{ $news->id }}">{{ trans('main.edit') }}</a></li>
                <li class="breadcrumb-item"><a href="/admin/news/delete/{{ $news->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('news.confirm_delete') }}')">{{ trans('main.delete') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->image)
        <div class="img">
            <a href="{{ $news->image }}">{!! resizeImage($news->image, ['width' => 100, 'alt' => $news->title]) !!}</a></div>
    @endif

    <div>{!! bbCode($news->text) !!}</div>

    <div style="clear:both;">
        {{ trans('main.added') }}: {!! $news->user->getProfile() !!} ({{ dateFixed($news->created_at) }})
    </div><br>

    @if ($comments->isNotEmpty())
        <div class="b"><i class="fa fa-comment"></i> <b>{{ trans('main.last_comments') }}</b></div>

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
            <i class="fas fa-comments"></i> <b><a href="/news/comments/{{ $news->id }}">{{ trans('news.all_comments') }}</a></b> ({{ $news->count_comments }})
            <a href="/news/end/{{ $news->id }}">&raquo;</a>
        </div>
    @endif

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {!! showError(trans('main.empty_comments')) !!}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/comments/{{ $news->id }}?read=1" method="post">
                    @csrf
                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ trans('main.message') }}:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_length') }}" name="msg" placeholder="{{ trans('main.message') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <button class="btn btn-success">{{ trans('main.write') }}</button>
                </form>
            </div>

            <br>
            <a href="/rules">{{ trans('main.rules') }}</a> /
            <a href="/stickers">{{ trans('main.stickers') }}</a> /
            <a href="/tags">{{ trans('main.tags') }}</a><br><br>
        @else
            {!! showError(trans('main.not_authorized')) !!}
        @endif
    @else
        {!! showError(trans('news.closed_news')) !!}
    @endif
@stop
