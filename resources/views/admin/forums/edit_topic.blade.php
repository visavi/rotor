@extends('layout')

@section('title')
    {{ trans('forums.title_edit_topic') }} {{ $topic->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ trans('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_edit_topic') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/topics/edit/{{ $topic->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ trans('forums.topic') }}:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $topic->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('note') }}">
                <label for="note">{{ trans('forums.note') }}:</label>
                <textarea class="form-control markItUp" id="note" name="note" rows="3">{{ getInput('note', $topic->note) }}</textarea>
                <div class="invalid-feedback">{{ textError('note') }}</div>
            </div>

            <div class="form-group{{ hasError('moderators') }}">
                <label for="moderators">{{ trans('forums.topic_curators') }} {{ trans('forums.curators_note') }}:</label>
                <input class="form-control" name="moderators" id="moderators" maxlength="100" value="{{ getInput('moderators', $topic->moderators) }}">
                <div class="invalid-feedback">{{ textError('moderators') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="locked">
                <input type="checkbox" class="custom-control-input" value="1" name="locked" id="locked"{{ getInput('locked', $topic->locked) ? ' checked' : '' }}>
                <label class="custom-control-label" for="locked">{{ trans('main.lock') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $topic->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>
@stop
