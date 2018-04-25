@extends('layout')

@section('title')
    Заметка для пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Заметка для пользователя {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">Заметка</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/users/{{ $user->login }}/note" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('notice') }}">
                <label for="notice">Сообщение:</label>
                <textarea class="form-control markItUp" id="notice" rows="5" name="notice" required>{{ getInput('notice', $user->note->text) }}</textarea>
                {!! textError('notice') !!}
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>


    <i class="fa fa-arrow-circle-left"></i> <a href="/users/{{ $user->login }}">Вернуться</a><br>
@stop
