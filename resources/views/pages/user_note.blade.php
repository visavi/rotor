@extends('layout')

@section('title')
    Заметка для пользователя {{ $user->login }} - @parent
@stop

@section('content')

    <h1>Заметка для пользователя {{ $user->login }}</h1>
    <div class="form">
        <form action="/user/{{ $user->login }}/note" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('notice') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="notice" required>{{ getInput('notice', $note['text']) }}</textarea>
                {!! textError('notice') !!}
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>


    <i class="fa fa-arrow-circle-left"></i> <a href="/user/{{ $user->login }}">Вернуться</a><br>
@stop
