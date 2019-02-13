@extends('layout')

@section('title')
    Редактирование темы {{ $topic->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">Форум</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование темы</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/topics/edit/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Тема:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $topic->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('note') }}">
                <label for="note">Объявление:</label>
                <textarea class="form-control markItUp" id="note" name="note" rows="3">{{ getInput('note', $topic->note) }}</textarea>
                {!! textError('note') !!}
            </div>

            <div class="form-group{{ hasError('moderators') }}">
                <label for="moderators">Кураторы темы (ID пользователей через запятую):</label>
                <input class="form-control" name="moderators" id="moderators" maxlength="100" value="{{ getInput('moderators', $topic->moderators) }}">
                {!! textError('moderators') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="locked">
                <input type="checkbox" class="custom-control-input" value="1" name="locked" id="locked"{{ getInput('locked', $topic->locked) ? ' checked' : '' }}>
                <label class="custom-control-label" for="locked">Закрепить тему</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $topic->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть тему</label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
