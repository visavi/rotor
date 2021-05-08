@extends('layout')

@section('title', __('forums.title_edit_topic'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_topic') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->getName() }}</b>
    <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>

    <div class="section-form mb-3 shadow">
        <form action="/topics/edit/{{ $topic->id }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('forums.topic') }}:</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="{{ __('forums.topic') }}" value="{{ getInput('title', $topic->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            @if ($post)
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('forums.post') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>
            @endif

            @if ($vote)
                <div class="mb-3{{ hasError('question') }}">
                    <label for="question" class="form-label">{{ __('forums.question') }}:</label>
                    <input class="form-control" name="question" id="question" maxlength="100" value="{{ getInput('question', $vote->title) }}" required>
                    <div class="invalid-feedback">{{ textError('question') }}</div>
                </div>

                @if (! $vote->count)
                    @include('votes/_answers')
                @endif
            @endif

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
