@extends('layout')

@section('title')
    Редактирование ссылки
@stop

@section('content')

    <h1>Редактирование ссылки</h1>

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

                <div class="input-group picker">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $link->color) }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <label>
                <input name="bold" class="js-bold" type="checkbox" value="1" {{ getInput('bold', $link->bold) == 1 ? ' checked' : '' }}> Жирность
            </label>
            <br/>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
