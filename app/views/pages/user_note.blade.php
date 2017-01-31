@extends('layout')

@section('title', 'Заметка для пользователя '.$user.' - @parent')

@section('content')

    <h1>Заметка для пользователя {{ $user }}</h1>
    <div class="form">
        <form action="/user/{{ $user }}/note" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ App::hasError('notice') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="notice" required>{{ App::getInput('notice', $note['text']) }}</textarea>
                {!! App::textError('notice') !!}
            </div>

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div><br />


    <i class="fa fa-arrow-circle-left"></i> <a href="/user/{{ $user }}">Вернуться</a><br />
@stop
