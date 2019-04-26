@extends('layout')

@section('title')
   {{ $offer->title }} - {{ trans('main.comments') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">{{ trans('offers.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
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
                            @if (getUser('id') !== $data->user_id)
                                <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ trans('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ trans('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Offer::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" data-toggle="tooltip" title="{{ trans('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if ($data->created_at + 600 > SITETIME && getUser('id') === $data->user->id)
                                <a href="/offers/edit/{{ $offer->id }}/{{ $data->id }}?page={{ $page->current }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            @endif

                            @if (isAdmin())
                                <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Offer::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                            @endif
                        </div>
                    @endif

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
        @if (empty($offer->closed))
            <div class="form">
                <form action="/offers/comments/{{ $offer->id }}" method="post">
                    @csrf
                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">{{ trans('main.message') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <button class="btn btn-success">{{ trans('main.write') }}</button>
                </form>
            </div><br>

            <a href="/rules">{{ trans('main.rules') }}</a> /
            <a href="/stickers">{{ trans('main.stickers') }}</a> /
            <a href="/tags">{{ trans('main.tags') }}</a><br><br>

        @else
            {!! showError(trans('offers.closed_comments')) !!}
        @endif
    @else
        {!! showError(trans('main.not_authorized')) !!}
    @endif
@stop
