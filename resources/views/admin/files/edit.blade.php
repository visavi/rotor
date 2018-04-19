@extends('layout')

@section('title')
    {{ 'Редактирование файла '.$path.$fileName.'.blade.php' }}
@stop

@section('content')

    <h1>Редактирование файла {{ $path.$fileName }}.blade.php</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/files">Редактирование страниц</a></li>
            <li class="breadcrumb-item"><a href="/admin/files?path={{ $path }}">{{ $path }}</a></li>
            <li class="breadcrumb-item active">Редактирование файла</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <textarea class="form-control" id="markItUpHtml" rows="25" name="msg" required>{{ getInput('msg', $contest) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">Нажмите Ctrl+Enter для перевода строки, Shift+Enter для вставки линии</p>
@stop
