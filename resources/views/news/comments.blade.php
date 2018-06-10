@extends('layout')

@section('title')
    {{ $news->title }} - {{ trans('news.comments_title', ['page' => $page->current]) }}
@stop

@section('content')

    <h1>{{ $news->title }} - {{ trans('news.comments') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('news.header') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.comments') }}</li>
        </ol>
    </nav>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post" id="comment_{{ $data->id }}">
                <div class="b">
                    <div class="img">
                        {!! $data->user->getAvatar() !!}
                        {!! $data->user->getOnline() !!}
                    </div>

                    @if (getUser())
                        <div class="float-right">
                            @if (getUser('id') != $data->user_id)
                                <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ trans('common.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ trans('common.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\News::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" data-toggle="tooltip" title="{{ trans('common.complain') }}"><i class="fa fa-bell text-muted"></i></a>

                            @endif

                            @if ($data->user_id == getUser('id') && $data->created_at + 600 > SITETIME)
                                <a href="/news/edit/{{ $news->id }}/{{ $data->id }}?page={{ $page->current }}" data-toggle="tooltip" title="{{ trans('common.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            @endif

                            @if (isAdmin())
                                <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\News::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('common.delete') }}"><i class="fa fa-times text-muted"></i></a>
                            @endif
                        </div>
                    @endif

                    <b>{!! $data->user->getProfile() !!}</b>
                    <small> ({{ dateFixed($data->created_at) }})</small><br>
                    {!! $data->user->getStatus() !!}
                </div>

                <div class="message">
                    {!! bbCode($data->text) !!}<br>
                </div>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @endif

    @if (! $news->closed)

        @if ($comments->isEmpty())
            {!! showError(trans('news.empty_comments')) !!}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/comments/{{ $news->id }}" method="post">
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
