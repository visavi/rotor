@extends('layout')

@section('title', $offer->title)

@section('header')
    <div class="float-end">
        @if (getUser())
            @if (in_array($offer->status, ['wait', 'process']) && getUser('id') === $offer->user_id)
                <a class="btn btn-success" title="{{ __('main.edit') }}" href="/offers/edit/{{ $offer->id }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/offers/{{ $offer->id }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $offer->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ $offer->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <div class="section-content">
            <div class="section-message">
                {{ bbCode($offer->text) }}
            </div>
        </div>

        <div class="section-body">
            {{ __('main.added') }}: {{ $offer->user->getProfile() }}
            <small class="section-date text-muted font-italic">{{ dateFixed($offer->created_at) }}</small>

            <div class="my-3">
                {{ $offer->getStatus() }}
            </div>

            <div class="js-rating">
                {{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $offer->user_id)
                    <a class="post-rating-down{{ $offer->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ $offer->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
                @endif
                <b>{{ formatNum($offer->rating) }}</b>
                @if (getUser() && getUser('id') !== $offer->user_id)
                    <a class="post-rating-up{{ $offer->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ $offer->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
                @endif
            </div>
        </div>
    </div>

    @if ($offer->reply)
        <div class="section mb-3 shadow">
            <h5>{{ __('offers.official_response') }}</h5>
            <div class="section-message">
                {{ bbCode($offer->reply) }}<br>
                {{ $offer->replyUser->getProfile() }}
                <small class="section-date text-muted font-italic">{{ dateFixed($offer->updated_at) }}</small>
            </div>
        </div>
    @endif

    @if ($offer->lastComments->isNotEmpty())
        <h5><i class="fa fa-comment"></i> {{ __('main.last_comments') }}</h5>

        @foreach ($offer->lastComments(5)->get() as $comment)
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
                        {{ bbCode($comment->text) }}<br>
                        @if (isAdmin())
                            <div class="small text-muted font-italic mt-2">{{ $comment->brow }}, {{ $comment->ip }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="p-3 mb-3 shadow">
            <i class="fas fa-comments"></i> <b><a href="/offers/comments/{{ $offer->id }}">{{ __('main.all_comments') }}</a></b> ({{ $offer->count_comments }})
            <a href="/offers/end/{{ $offer->id }}">&raquo;</a>
        </div>
    @endif

    @if (! $offer->closed)
        @if ($offer->lastComments->isEmpty())
            {{ showError(__('main.empty_comments')) }}
        @endif

        @if (getUser())
            <div class="section-form mb-3 shadow">
                <form action="/offers/comments/{{ $offer->id }}" method="post">
                    @csrf
                    <div class="mb-3{{ hasError('msg') }}">
                        <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <button class="btn btn-primary">{{ __('main.write') }}</button>
                </form>
            </div>

            <a href="/rules">{{ __('main.rules') }}</a> /
            <a href="/stickers">{{ __('main.stickers') }}</a> /
            <a href="/tags">{{ __('main.tags') }}</a><br><br>
        @else
            {{ showError(__('main.closed_comments')) }}
        @endif
    @else
        {{ showError(__('main.not_authorized')) }}
    @endif
@stop
