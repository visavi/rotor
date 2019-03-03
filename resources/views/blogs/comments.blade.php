@extends('layout')

@section('title')
    {{ $blog->title }} - {{ trans('main.comments') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/articles/{{ $blog->id }}">{{ $blog->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/articles/rss/{{ $blog->id }}">{{ trans('main.rss') }}</a><hr>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post" id="comment_{{ $data->id }}">
                <div class="b">
                    <div class="img">
                        {!! $data->user->getAvatar() !!}
                        {!! $data->user->getOnline() !!}
                    </div>

                    <div class="float-right">
                        @if (getUser('id') !== $data->user_id)
                            <a href="#" onclick="return postReply(this)" title="{{ trans('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="{{ trans('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Blog::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" title="{{ trans('main.complaint') }}"><i class="fa fa-bell text-muted"></i></a>
                        @endif

                        @if ($data->created_at + 600 > SITETIME && getUser('id') === $data->user->id)
                            <a href="/articles/edit/{{ $blog->id }}/{{ $data->id }}?page={{ $page->current }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        @endif

                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Blog::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! $data->user->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
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
    @else
        {!! showError(trans('main.empty_comments')) !!}
    @endif

    @if (getUser())
        <div class="form">
            <form action="/articles/comments/{{ $blog->id }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ trans('blogs.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                    <span class="js-textarea-counter"></span>
                    {!! textError('msg') !!}
                </div>

                <button class="btn btn-success">{{ trans('main.write') }}</button>
            </form>
        </div><br>

        <a href="/rules">{{ trans('main.rules') }}</a> /
        <a href="/stickers">{{ trans('main.stickers') }}</a> /
        <a href="/tags">{{ trans('main.tags') }}</a><br><br>

    @else
        {!! showError(trans('main.not_authorized')) !!}
    @endif
@stop
