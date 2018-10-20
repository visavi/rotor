@extends('layout')

@section('title')
    Обратная связь
@stop

@section('content')

    <h1>Обратная связь</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Обратная связь</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post" action="/mails">

            @if (! getUser())
                <div class="form-group{{ hasError('name') }}">
                    <label for="inputName">Ваше имя:</label>
                    <input type="text" class="form-control" id="inputName" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                    {!! textError('name') !!}
                </div>
            @endif

            @if (empty(getUser('email')))
                <div class="form-group{{ hasError('email') }}">
                    <label for="inputEmail">Ваш email:</label>
                    <input type="text" class="form-control" id="inputEmail" name="email" maxlength="50" value="{{ getInput('email') }}" required>
                    {!! textError('email') !!}
                </div>
            @endif

            <div class="form-group{{ hasError('message') }}">
                <label for="message">Сообщение:</label>
                <textarea class="form-control markItUp" id="message" rows="5" name="message" required>{{ getInput('message') }}</textarea>
                {!! textError('message') !!}
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">Отправить</button>
        </form>
    </div><br>

    Обновите страницу если вы не видите проверочный код!<br><br>

@stop
