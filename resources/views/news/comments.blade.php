@extends('layout')

@section('title', $news->title . ' - ' . __('news.comments_title') . ' (' . __('main.page_num', ['page' => $comments->currentPage()]) . ')')

@section('header')
    <h1>{{ $news->title }} - {{ __('main.comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <div class="section mb-3 shadow" id="comment_{{ $comment->id }}">
                <div class="user-avatar">
                    {!! $comment->user->getAvatar() !!}
                    {!! $comment->user->getOnline() !!}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {!! $comment->user->getProfile() !!}
                        <small class="section-date text-muted font-italic">{{ dateFixed($comment->created_at) }}</small><br>
                        <small class="font-italic">{!! $comment->user->getStatus() !!}</small>
                    </div>

                    @if (getUser())
                        <div class="text-right">
                            @if (getUser('id') !== $comment->user_id)
                                <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ $comment->relate->getMorphClass() }}" data-id="{{ $comment->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $comments->currentPage() }}" rel="nofollow" data-toggle="tooltip" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>

                            @endif

                            @if ($comment->created_at + 600 > SITETIME && getUser('id') === $comment->user_id)
                                <a href="/news/edit/{{ $news->id }}/{{ $comment->id }}?page={{ $comments->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            @endif

                            @if (isAdmin())
                                <a href="#" onclick="return deleteComment(this)" data-rid="{{ $comment->relate_id }}" data-id="{{ $comment->id }}" data-type="{{ $comment->relate->getMorphClass() }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {!! bbCode($comment->text) !!}
                    </div>

                @if (isAdmin())
                    <div class="small text-muted font-italic mt-2">{{ $comment->brow }}, {{ $comment->ip }}</div>
                @endif
                </div>
            </div>
        @endforeach
    @endif

    {{ $comments->links() }}

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {!! showError(__('main.empty_comments')) !!}
        @endif

        @if (getUser())
            <div class="section-form mb-3 shadow">
                <form action="/news/comments/{{ $news->id }}" method="post">
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
