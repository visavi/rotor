@extends('layout')

@section('title')
    Мой профиль - @parent
@stop

@section('content')

    <h1>Мой профиль</h1>

    <i class="fa fa-book"></i>
    <a href="/user/{{ App::getUsername() }}">Моя анкета</a> /
    <b>Мой профиль</b> /
    <a href="/account">Мои данные</a> /
    <a href="/setting">Настройки</a><hr>

    <div class="form">
        <form method="post" action="/profile">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

            <div class="container-fluid">
                <div class="row">

                    <div class="col-md-6 col-md-push-6">
                        <div class="pull-right">
                            @if (!empty(App::user('picture')) && file_exists(HOME.'/uploads/photos/'.App::user('picture')))
                                <a class="gallery" href="/uploads/photos/{{ App::user('picture') }}">
                                    {!! resize_image('uploads/photos/', App::user('picture'), Setting::get('previewsize'), ['alt' => App::user('login'), 'class' => 'img-responsive img-rounded']) !!}
                                </a>
                                <a href="/pictures">Изменить</a> / <a href="/pictures/delete?token={{ $_SESSION['token'] }}">Удалить</a>
                            @else
                                <img class="img-responsive img-rounded" src="/assets/img/images/photo.jpg" alt="Фото">
                                <a href="/pictures">Загрузить фото</a>
                            @endif
                            </div>
                        </div>

                    <div class="col-md-6 col-md-pull-6">

                        <div class="form-group{{ App::hasError('msg') }}">
                            <label for="inputName">Имя:</label>
                            <input class="form-control" id="inputName" name="name" maxlength="20" value="{{ App::getInput('name', App::user('name')) }}">
                            {!! App::textError('name') !!}
                        </div>

                        <div class="form-group{{ App::hasError('country') }}">
                            <label for="inputCountry">Страна:</label>
                            <input class="form-control" id="inputCountry" name="country" maxlength="30" value="{{ App::getInput('country', App::user('country')) }}">
                            {!! App::textError('country') !!}
                        </div>

                        <div class="form-group{{ App::hasError('city') }}">
                            <label for="inputCity">Город:</label>
                            <input class="form-control" id="inputCity" name="city" maxlength="50" value="{{ App::getInput('city', App::user('city')) }}">
                            {!! App::textError('city') !!}
                        </div>

                        <div class="form-group{{ App::hasError('icq') }}">
                            <label for="inputIcq">ICQ:</label>
                            <input class="form-control" id="inputIcq" name="icq" maxlength="10" value="{{ App::getInput('icq', App::user('icq')) }}">
                            {!! App::textError('icq') !!}
                        </div>

                        <div class="form-group{{ App::hasError('skype') }}">
                            <label for="inputSkype">Skype:</label>
                            <input class="form-control" id="inputSkype" name="skype" maxlength="32" value="{{ App::getInput('skype', App::user('skype')) }}">
                            {!! App::textError('skype') !!}
                        </div>

                        <div class="form-group{{ App::hasError('site') }}">
                            <label for="inputSite">Сайт:</label>
                            <input class="form-control" id="inputSite" name="site" maxlength="50" value="{{ App::getInput('site', App::user('site')) }}">
                            {!! App::textError('site') !!}
                        </div>


                        <div class="form-group{{ App::hasError('birthday') }}">
                            <label for="inputBirthday">Дата рождения (дд.мм.гггг):</label>
                            <input class="form-control" id="inputBirthday" name="birthday" maxlength="10" value="{{ App::getInput('birthday', App::user('birthday')) }}">
                            {!! App::textError('birthday') !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group{{ App::hasError('info') }}">
                            <label for="markItUp">О себе:</label>
                            <textarea class="form-control" id="markItUp" cols="25" rows="5" name="info">{{ App::getInput('info', App::user('info')) }}</textarea>
                            {!! App::textError('info') !!}
                        </div>
                        <button class="btn btn-primary">Изменить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
