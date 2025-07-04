@extends('layout')

@section('title', __('forums.title_edit_post'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>

            @foreach ($post->topic->forum->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('forums.forum', ['id' => $parent->id]) }}">{{ $parent->title }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('topics.topic', ['id' => $post->topic->id]) }}">{{ $post->topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_post') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->getName() }}</b>
    <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('posts.edit', ['id' => $post->id, 'page' => $page]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('forums.post') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('forum_text_max') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @include('app/_upload_file', ['model' => $post])

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
