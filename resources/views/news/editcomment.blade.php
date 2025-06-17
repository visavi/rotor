@extends('layout')

@section('title', __('news.edit_title'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.index') }}">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.view', ['id' => $news->id]) }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.comments', ['id' => $news->id]) }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('news.edit_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->getName() }}</b>
    <small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>

    <div class="section-form mb-3 shadow">
        <form method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_text_max') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $comment->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
