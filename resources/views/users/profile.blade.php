@extends('layout')

@section('title')
    Мой профиль
@stop

@section('content')

    <h1>Мой профиль</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">Мой профиль</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post" action="/profile">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group{{ hasError('name') }}">
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

                        <div class="form-group{{ hasError('phone') }}">
                            <label for="inputPhone">Телефон:</label>
                            <input class="form-control" id="inputPhone" name="phone" maxlength="15" value="{{ getInput('phone', $user->phone) }}">
                            {!! textError('phone') !!}
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

                        <?php $inputGender = getInput('gender', $user->gender); ?>
                        Пол:
                        <div class="form-group{{ hasError('gender') }}">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender == 'male' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderMale">Мужской</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender == 'female' ? ' checked' : '' }}>
                                <label class="custom-control-label" for="inputGenderFemale">Женский</label>
                            </div>
                            {!! textError('gender') !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="float-right">
                            @if ($user->picture && file_exists(HOME . $user->picture))
                                <a class="gallery" href="{{ getUser('picture') }}">
                                    {!! resizeImage(getUser('picture'), ['alt' => $user->login, 'class' => 'img-fluid rounded']) !!}
                                </a><br>
                                <a href="/pictures">Изменить</a> / <a href="/pictures/delete?token={{ $_SESSION['token'] }}">Удалить</a>
                            @else
                                <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Фото"><br>
                                <a href="/pictures">Загрузить фото</a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group{{ hasError('info') }}">
                            <label for="info">О себе:</label>
                            <textarea class="form-control markItUp" id="info" cols="25" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                            {!! textError('info') !!}
                        </div>
                        <button class="btn btn-primary">Изменить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
