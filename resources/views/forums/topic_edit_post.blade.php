@extends('layout')

@section('title')
    {{ __('forums.title_edit_post') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>

            @if ($post->topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $post->topic->forum->parent->id }}">{{ $post->topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $post->topic->id }}">{{ $post->topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_post') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="section-form p-2 shadow">
        <form action="/posts/edit/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('forums.post') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @if ($post->files->isNotEmpty())
                <i class="fa fa-paperclip"></i> <b>{{ __('forums.delete_files') }}:</b><br>
                @foreach ($post->files as $file)
                    <input type="checkbox" name="delfile[]" value="{{ $file->id }}">
                    <a href="{{ $file->hash }}" target="_blank">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                @endforeach
                <br>
            @endif

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
