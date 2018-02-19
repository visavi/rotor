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
                <input class="form-control" id="site" name="site" type="text" value="{{ $link->site }}" maxlength="50">
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название ссылки:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ $link->name }}">
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">Код цвета:</label>
                <input class="form-control" id="color" name="color" type="text" maxlength="7" value="{{ $link->color }}">
                {!! textError('color') !!}
            </div>

            <label>
                <input name="bold" class="js-bold" type="checkbox" value="1" {{ $link->bold == 1 ? ' checked' : '' }}> Жирность
            </label>
            <br/>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
