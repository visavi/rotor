@extends('layout')

@section('title')
    {{ trans('news.edit_title') }}
@stop

@section('content')

    <h1>{{ trans('news.edit_title') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('news.header') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item"><a href="/news/comments/{{ $news->id }}">{{ trans('news.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.editing') }}</li>
        </ol>
    </nav>

    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('news.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('news.message_text') }}" required>{{ getInput('msg', $comment->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-success">{{ trans('news.edit') }}</button>
        </form>
    </div>
@stop
