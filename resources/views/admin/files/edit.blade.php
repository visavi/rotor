@extends('layout')

@section('title')
    {{ 'Редактирование файла '.$path.$file.'.blade.php' }}
@stop

@section('content')

    <h1>Редактирование файла {{ $path.$file }}.blade.php</h1>

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

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path={{ $path }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
