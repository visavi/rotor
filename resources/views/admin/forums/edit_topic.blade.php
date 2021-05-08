@extends('layout')

@section('title', __('forums.title_edit_topic') . ' ' . $topic->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ __('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/topics/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_topic') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/topics/edit/{{ $topic->id }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('title') }}">
                <label for="title" class="form-label">{{ __('forums.topic') }}:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $topic->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('note') }}">
                <label for="note" class="form-label">{{ __('forums.note') }}:</label>
                <textarea class="form-control markItUp" id="note" name="note" rows="3">{{ getInput('note', $topic->note) }}</textarea>
                <div class="invalid-feedback">{{ textError('note') }}</div>
            </div>

            <div class="mb-3{{ hasError('moderators') }}">
                <label for="moderators" class="form-label">{{ __('forums.topic_curators') }}:</label>
                <input class="form-control" name="moderators" id="moderators" maxlength="100" value="{{ getInput('moderators', $topic->moderators) }}">
                <span class="text-muted font-italic">{{ __('forums.curators_note') }}</span>
                <div class="invalid-feedback">{{ textError('moderators') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="locked">
                <input type="checkbox" class="form-check-input" value="1" name="locked" id="locked"{{ getInput('locked', $topic->locked) ? ' checked' : '' }}>
                <label class="form-check-label" for="locked">{{ __('main.lock') }}</label>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="form-check-input" value="1" name="closed" id="closed"{{ getInput('closed', $topic->closed) ? ' checked' : '' }}>
                <label class="form-check-label" for="closed">{{ __('main.close') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
