@extends('layout')

@section('title')
    Редактирование пользователя {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">Пользователи</a></li>
            <li class="breadcrumb-item active">Редактирование пользователя</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getProfile() !!} {{ $user->login }} #{{ $user->id }}</h3>

    @if ($user->id === getUser('id'))
        <b><span style="color:#ff0000">Внимание! Вы редактируете собственный аккаунт!</span></b><br><br>
    @endif

    <div class="form">
        <form method="post" action="/admin/users/edit?user={{ $user->login }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <?php $inputLevel = getInput('level', $user->level); ?>

            <div class="form-group">
                <label for="level">Уровень:</label>
                <select class="form-control" id="level" name="level">
                    @foreach($allGroups as $key => $level)
                        <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                    @endforeach
                </select>
                {!! textError('level') !!}
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="password">Новый пароль:</label>
                <input type="text" class="form-control" id="password" name="password" maxlength="50" value="{{ getInput('password') }}">
                {!! textError('password') !!}
                <span class="text-muted font-italic">Оставьте пустым если не нужно менять</span>
            </div>

            <div class="form-group{{ hasError('email') }}">
                <label for="email">Email:</label>
                <input type="text" class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                {!! textError('email') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Имя пользователя:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                {!! textError('name') !!}
            </div>


            <div class="form-group{{ hasError('country') }}">
                <label for="country">Страна:</label>
                <input type="text" class="form-control" id="country" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                {!! textError('country') !!}
            </div>

            <div class="form-group{{ hasError('city') }}">
                <label for="city">Город:</label>
                <input type="text" class="form-control" id="city" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                {!! textError('city') !!}
            </div>

            <div class="form-group{{ hasError('site') }}">
                <label for="site">Сайт:</label>
                <input type="text" class="form-control" id="site" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('created') }}">
                <label for="created">Зарегистрирован:</label>
                <input type="text" class="form-control" id="created" name="created" maxlength="10" value="{{ getInput('created', dateFixed($user->created_at, 'd.m.Y')) }}" required>
                {!! textError('created') !!}
            </div>

            <div class="form-group{{ hasError('birthday') }}">
                <label for="birthday">Дата рождения:</label>
                <input type="text" class="form-control" id="birthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                {!! textError('birthday') !!}
            </div>

            <div class="form-group{{ hasError('icq') }}">
                <label for="icq">ICQ:</label>
                <input type="text" class="form-control" id="icq" name="icq" maxlength="10" value="{{ getInput('icq', $user->icq) }}">
                {!! textError('icq') !!}
            </div>

            <div class="form-group{{ hasError('skype') }}">
                <label for="skype">Skype:</label>
                <input type="text" class="form-control" id="skype" name="skype" maxlength="31" value="{{ getInput('skype', $user->skype) }}">
                {!! textError('skype') !!}
            </div>

            <div class="form-group{{ hasError('point') }}">
                <label for="point">Актив:</label>
                <input type="text" class="form-control" id="point" name="point" maxlength="10" value="{{ getInput('point', $user->point) }}" required>
                {!! textError('point') !!}
            </div>

            <div class="form-group{{ hasError('money') }}">
                <label for="money">Деньги:</label>
                <input type="text" class="form-control" id="money" name="money" maxlength="15" value="{{ getInput('money', $user->money) }}" required>
                {!! textError('money') !!}
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">Статус:</label>
                <input type="text" class="form-control" id="status" name="status" maxlength="25" value="{{ getInput('status', $user->status) }}">
                {!! textError('status') !!}
            </div>

            <div class="form-group{{ hasError('posrating') }}">
                <label for="posrating">Репутация (плюсы):</label>
                <input type="text" class="form-control" id="posrating" name="posrating" maxlength="10" value="{{ getInput('posrating', $user->posrating) }}" required>
                {!! textError('posrating') !!}
            </div>

            <div class="form-group{{ hasError('negrating') }}">
                <label for="negrating">Репутация (минусы):</label>
                <input type="text" class="form-control" id="negrating" name="negrating" maxlength="10" value="{{ getInput('negrating', $user->negrating) }}" required>
                {!! textError('negrating') !!}
            </div>

            <?php $inputThemes = getInput('themes', $user->themes); ?>
            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">Тема:</label>

                <select class="form-control" name="themes" id="themes">
                    <option value="0">Автоматически</option>

                    @foreach ($allThemes as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                {!! textError('themes') !!}
            </div>

            <?php $inputGender = getInput('gender', $user->gender); ?>
            Пол:
            <div class="form-group{{ hasError('gender') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderMale">Мужской</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderFemale">Женский</label>
                </div>
                {!! textError('gender') !!}
            </div>

            <div class="form-group{{ hasError('info') }}">
                <label for="info">О себе:</label>
                <textarea class="form-control markItUp" id="info" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                {!! textError('info') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <div class="b"><b>Дополнительная информация</b></div>

    @if ($user->level === 'pended')
        <b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">Внимание, данный пользователь заблокирован!</span></b><br>
            До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($banhist)
                Срок: {{ formatTime($banhist->term) }}<br>
                Причина: {!! bbCode($banhist->reason) !!}<br>
                Забанил: {!! $banhist->sendUser->getProfile() !!}<br>
            @endif
        </div>
    @endif

    Последний визит: {{ dateFixed($user->updated_at, 'j F Y / H:i') }}<br><br>

    @if (! in_array($user->level, $adminGroups, true))
        <i class="fa fa-times"></i> <a href="/admin/users/delete?user={{ $user->login }}">Удалить профиль</a><br>
    @endif
@stop
