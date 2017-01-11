@extends('layout')

@section('title', 'Заметка для пользователя '.$uz.' - @parent')

@section('content')

    <h1>Заметка для пользователя {{ $uz }}</h1>
    <div class="form">
        <form action="/user/{{ $uz }}/note" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ App::hasError('note') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="note" required>{{ App::getInput('note', $note['text']) }}</textarea>
                {!! App::textError('note') !!}
            </div>

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div><br />


    <i class="fa fa-arrow-circle-left"></i> <a href="/user/{{ $uz }}">Вернуться</a><br />
@stop
