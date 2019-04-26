@extends('layout')

@section('title')
    {{ trans('offers.editing_comment') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">{{ trans('offers.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item"><a href="/offers/comments/{{ $offer->id }}">{{ trans('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('offers.editing_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form action="/offers/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('main.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
