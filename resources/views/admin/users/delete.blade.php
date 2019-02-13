@extends('layout')

@section('title')
    Удаление пользователя {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">Пользователи</a></li>
            <li class="breadcrumb-item"><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">Удаление пользователя</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/users/delete?user={{ $user->login }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <b>Добавить в черный список:</b><br>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="loginblack" id="loginblack" checked>
                <label class="custom-control-label" for="loginblack">Логин пользователя</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="mailblack" id="mailblack" checked>
                <label class="custom-control-label" for="mailblack">Email пользователя</label>
            </div>

            <b>Удаление активности:</b><br>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="deltopics" id="deltopics">
                <label class="custom-control-label" for="deltopics">Темы в форуме</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delposts" id="delposts">
                <label class="custom-control-label" for="delposts">Сообщения в форуме</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delcomments" id="delcomments">
                <label class="custom-control-label" for="delcomments">Комментарии</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="delimages" id="delimages">
                <label class="custom-control-label" for="delimages">Фотографии в галерее</label>
            </div>

            <button class="btn btn-danger">Удалить пользователя</button>
        </form>
    </div>
@stop
