@extends('layout')

@section('title')
    Рассылка приватных сообщений
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">Рассылка сообщений</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/delivery" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Текст сообщения:</label>
                <textarea rows="5" class="form-control markItUp" id="msg" name="msg" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            Отправить:<br>
            <?php $inputType = (int) getInput('type', 1); ?>

            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType1">В онлайне</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType === 2 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType2">Активным</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType3" name="type" value="3"{{ $inputType === 3 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType3">Администрации</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType4" name="type" value="4"{{ $inputType === 4 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType4">Всем пользователям</label>
            </div>

            <button class="btn btn-primary">Разослать</button>
        </form>
    </div>
@stop
