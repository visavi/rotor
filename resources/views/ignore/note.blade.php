@extends('layout')

@section('title')
    Заметка для {{ $ignore->ignoring->login }}
@stop

@section('content')

    <h1>Заметка для {{ $ignore->ignoring->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ getUser('login') }}">{{ getUser('login') }}</a></li>
            <li class="breadcrumb-item"><a href="/contacts">Игнор-лист</a></li>
            <li class="breadcrumb-item active">Заметка</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post" action="/ignores/note/{{ $ignore->id }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Заметка:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg">{{ getInput('msg', $ignore->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
    <br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/ignores">Вернуться</a><br>
@stop
