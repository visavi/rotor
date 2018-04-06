@extends('layout')

@section('title')
    Поиск в блогах
@stop

@section('content')

    <h1>Поиск в блогах</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>
            <li class="breadcrumb-item active">Поиск в блогах</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/blog/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            Искать:
            <?php $inputWhere = getInput('where'); ?>
            <div class="form-group{{ hasError('where') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere == 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere0">В темах</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere == 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere1">В сообщениях</label>
                </div>
                {!! textError('where') !!}
            </div>

            Тип запроса:
            <?php $inputType = getInput('type'); ?>
            <div class="form-group{{ hasError('type') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType0" name="type" value="0"{{ $inputType == 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType0">И</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType == 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType1">Или</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType == 2 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType2">Полный</label>
                </div>
                {!! textError('type') !!}
            </div>

            <button class="btn btn-primary">Поиск</button>
        </form>
    </div>
@stop
