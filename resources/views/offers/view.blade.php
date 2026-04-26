@extends('layout')

@section('title', $offer->title)

@section('header')
    <div class="float-end">
        @if (getUser())
            @if (in_array($offer->status, ['wait', 'process']) && getUser('id') === $offer->user_id)
                <a class="btn btn-success" title="{{ __('main.edit') }}" href="{{ route('offers.edit', ['id' => $offer->id]) }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-adaptive" href="{{ route('admin.offers.view', ['id' => $offer->id]) }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $offer->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.index', ['type' => $offer->type]) }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ $offer->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-content">
            <div class="section-message">
                {{ $offer->getText() }}
            </div>
        </div>

        <div class="section-body">
            {{ __('main.added') }}: {{ $offer->user->getProfile() }}
            <small class="section-date text-muted fst-italic">{{ dateFixed($offer->created_at) }}</small>

            <div class="my-3">
                {{ $offer->getStatus() }}
            </div>

            <div class="js-rating">
                {{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $offer->user_id)
                    <a class="post-rating-down{{ $offer->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ $offer->getMorphClass() }}" data-vote="-"><i class="fa fa-arrow-down"></i></a>
                @endif
                <b>{{ formatNum($offer->rating) }}</b>
                @if (getUser() && getUser('id') !== $offer->user_id)
                    <a class="post-rating-up{{ $offer->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ $offer->getMorphClass() }}" data-vote="+"><i class="fa fa-arrow-up"></i></a>
                @endif
            </div>
        </div>
    </div>

    @if ($offer->reply)
        <div class="section mb-3 shadow">
            <h5>{{ __('offers.official_response') }}</h5>
            <div class="section-message">
                {{ $offer->getReply() }}<br>
                {{ $offer->replyUser->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($offer->updated_at) }}</small>
            </div>
        </div>
    @endif

    <h5 id="comments"><i class="fa-regular fa-comment"></i> {{ __('main.comments') }}</h5>
    <hr>

    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'offers.edit-comment', 'parentId' => $offer->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', [
        'action' => route('offers.add-comment', ['id' => $offer->id]),
        'closed' => $offer->closed,
    ])
@stop
