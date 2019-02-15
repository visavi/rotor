@extends('layout')

@section('title')
    {{ trans('guestbooks.title_reply') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/guestbooks">{{ trans('guestbooks.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('guestbooks.title_reply') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="alert alert-info">
        <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br>
        <div>{{ trans('guestbooks.message') }}: {!! bbCode($post->text) !!}</div>
    </div>

    <div class="form">
        <form action="/admin/guestbooks/reply/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">{{ trans('guestbooks.message') }}:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $post->reply) }}</textarea>
                {!! textError('reply') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.write') }}</button>
        </form>
    </div>
@stop
