@extends('layout')

@section('title')
    Рассылка приватных сообщений
@stop

@section('content')

    <h1>Рассылка приватных сообщений</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Рассылка сообщений</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/admin/delivery" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Текст сообщения:</label>
                <textarea rows="5" class="form-control markItUp" id="msg" name="msg" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            Отправить:<br>
            <?php $inputType = getInput('type', 1); ?>
            <label><input name="type" type="radio" value="1"{{ $inputType == 1 ? ' checked' : '' }}> В онлайне</label><br>
            <label><input name="type" type="radio" value="2"{{ $inputType == 2 ? ' checked' : '' }}> Активным</label><br>
            <label><input name="type" type="radio" value="3"{{ $inputType == 3 ? ' checked' : '' }}> Администрации</label><br>
            <label><input name="type" type="radio" value="4"{{ $inputType == 4 ? ' checked' : '' }}> Всем пользователям</label><br>

            <button class="btn btn-primary">Разослать</button>
        </form>
    </div>
@stop
