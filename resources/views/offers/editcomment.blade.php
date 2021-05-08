@extends('layout')

@section('title', __('offers.editing_comment'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item"><a href="/offers/comments/{{ $offer->id }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.editing_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->getName() }}</b>
    <small class="section-date text-muted font-italic">{{ dateFixed($comment->created_at) }}</small><br>

    <div class="section-form mb-3 shadow">
        <form action="/offers/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
