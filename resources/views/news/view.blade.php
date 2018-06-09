@extends('layout')

@section('title')
    {{ $news->title }}
@stop

@section('description', stripString($news->text))

@section('content')

    <h1>{{ $news->title }} <small> ({{ dateFixed($news->created_at) }})</small></h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('news.header') }}</a></li>
            <li class="breadcrumb-item active">{{ $news->title }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/news/edit/{{ $news->id }}">{{ trans('common.edit') }}</a></li>
                <li class="breadcrumb-item"><a href="/admin/news/delete/{{ $news->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('news.confirm_delete') }}')">{{ trans('common.delete') }}</a></li>
            @endif
        </ol>
    </nav>

    @if ($news->image)
        <div class="img">
            <a href="/uploads/news/{{ $news->image }}">{!! resizeImage('/uploads/news/' . $news->image, ['width' => 100, 'alt' => $news->title]) !!}</a></div>
    @endif

    <div>{!! bbCode($news->text) !!}</div>

    <div style="clear:both;">
        {{ trans('news.added_by') }}: {!! profile($news->user) !!}
    </div><br>

    @if ($comments->isNotEmpty())
        <div class="b"><i class="fa fa-comment"></i> <b>{{ trans('news.last_comments') }}</b></div>

        @foreach ($comments as $comment)
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! userAvatar($comment->user) !!}
                        {!! userOnline($comment->user) !!}
                    </div>

                    <b>{!! profile($comment->user) !!}</b>
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
            {!! showError(trans('news.empty_comments')) !!}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/comments/{{ $news->id }}?read=1" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ trans('news.message') }}:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('news.message_text') }}" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    <button class="btn btn-success">{{ trans('news.write') }}</button>
                </form>
            </div>

            <br>
            <a href="/rules">{{ trans('common.rules') }}</a> /
            <a href="/smiles">{{ trans('common.smiles') }}</a> /
            <a href="/tags">{{ trans('common.tags') }}</a><br><br>
        @else
            {!! showError(trans('news.not_authorized')) !!}
        @endif
    @else
        {!! showError(trans('news.closed_news')) !!}
    @endif
@stop
