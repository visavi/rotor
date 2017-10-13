@extends('layout')

@section('title')
    Мой профиль
@stop

@section('content')

    <h1>Мой профиль</h1>

    <i class="fa fa-book"></i>
    <a href="/user/{{ $user->login }}">Моя анкета</a> /
    <b>Мой профиль</b> /
    <a href="/account">Мои данные</a> /
    <a href="/setting">Настройки</a><hr>

    <div class="form">
        <form method="post" action="/profile">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group{{ hasError('msg') }}">
                            <label for="inputName">Имя:</label>
                            <input class="form-control" id="inputName" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                            {!! textError('name') !!}
                        </div>

                        <div class="form-group{{ hasError('country') }}">
                            <label for="inputCountry">Страна:</label>
                            <input class="form-control" id="inputCountry" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                            {!! textError('country') !!}
                        </div>

                        <div class="form-group{{ hasError('city') }}">
                            <label for="inputCity">Город:</label>
                            <input class="form-control" id="inputCity" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                            {!! textError('city') !!}
                        </div>

                        <div class="form-group{{ hasError('icq') }}">
                            <label for="inputIcq">ICQ:</label>
                            <input class="form-control" id="inputIcq" name="icq" maxlength="10" value="{{ getInput('icq', $user->icq) }}">
                            {!! textError('icq') !!}
                        </div>

                        <div class="form-group{{ hasError('skype') }}">
                            <label for="inputSkype">Skype:</label>
                            <input class="form-control" id="inputSkype" name="skype" maxlength="32" value="{{ getInput('skype', $user->skype) }}">
                            {!! textError('skype') !!}
                        </div>

                        <div class="form-group{{ hasError('site') }}">
                            <label for="inputSite">Сайт:</label>
                            <input class="form-control" id="inputSite" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                            {!! textError('site') !!}
                        </div>


                        <div class="form-group{{ hasError('birthday') }}">
                            <label for="inputBirthday">Дата рождения (дд.мм.гггг):</label>
                            <input class="form-control" id="inputBirthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                            {!! textError('birthday') !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="float-right">
                            @if ($user->picture && file_exists(UPLOADS.'/photos/'.$user->picture))
                                <a class="gallery" href="/uploads/photos/{{ getUser('picture') }}">
                                    {!! resizeImage('uploads/photos/', getUser('picture'), setting('previewsize'), ['alt' => $user->login, 'class' => 'img-fluid rounded']) !!}
                                </a>
                                <a href="/pictures">Изменить</a> / <a href="/pictures/delete?token={{ $_SESSION['token'] }}">Удалить</a>
                            @else
                                <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Фото">
                                <a href="/pictures">Загрузить фото</a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group{{ hasError('info') }}">
                            <label for="markItUp">О себе:</label>
                            <textarea class="form-control" id="markItUp" cols="25" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                            {!! textError('info') !!}
                        </div>
                        <button class="btn btn-primary">Изменить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
