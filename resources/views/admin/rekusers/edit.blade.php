@extends('layout')

@section('title')
    Редактирование ссылки
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/reklama">Пользовательская реклама</a></li>
            <li class="breadcrumb-item active">Редактирование ссылки</li>
        </ol>
    </nav>
@stop

@section('content')s
    <div class="form">
        <form action="/admin/reklama/edit/{{ $link->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('site') }}">
                <label for="site">Адрес сайта:</label>
                <input class="form-control" id="site" name="site" type="text" value="{{ getInput('site', $link->site) }}" maxlength="50" required>
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название ссылки:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ getInput('name', $link->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">Код цвета:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $link->color) }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input" value="1" name="bold" id="bold"{{ getInput('bold', $link->bold) ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">Жирный текст</label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
