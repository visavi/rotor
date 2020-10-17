@extends('layout')

@section('title', __('news.edit_title'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item"><a href="/news/comments/{{ $news->id }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('news.edit_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->getName() }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="section-form p-2 shadow">
        <form method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_length') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $comment->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <button class="btn btn-success">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
