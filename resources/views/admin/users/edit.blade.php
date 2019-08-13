@extends('layout')

@section('title')
    {{ trans('users.edit_user') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ trans('index.users') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('users.edit_user') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getProfile() !!} {{ $user->login }} #{{ $user->id }}</h3>

    @if ($user->id === getUser('id'))
        <div class="p-1 my-1 bg-danger text-white">{{ trans('users.edit_user_notice') }}</div>
    @endif

    <div class="form">
        <form method="post" action="/admin/users/edit?user={{ $user->login }}">
            @csrf
            <?php $inputLevel = getInput('level', $user->level); ?>
            <div class="form-group">
                <label for="level">{{ trans('users.position') }}:</label>
                <select class="form-control" id="level" name="level">
                    @foreach($allGroups as $key => $level)
                        <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('level') }}</div>
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="password">{{ trans('users.new_password') }}:</label>
                <input type="text" class="form-control" id="password" name="password" maxlength="50" value="{{ getInput('password') }}">
                <div class="invalid-feedback">{{ textError('password') }}</div>
                <span class="text-muted font-italic">{{ trans('users.password_hint') }}</span>
            </div>

            <div class="form-group{{ hasError('email') }}">
                <label for="email">{{ trans('users.email') }}:</label>
                <input type="text" class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                <div class="invalid-feedback">{{ textError('email') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ trans('users.name') }}:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>


            <div class="form-group{{ hasError('country') }}">
                <label for="country">{{ trans('users.country') }}:</label>
                <input type="text" class="form-control" id="country" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                <div class="invalid-feedback">{{ textError('country') }}</div>
            </div>

            <div class="form-group{{ hasError('city') }}">
                <label for="city">{{ trans('users.city') }}:</label>
                <input type="text" class="form-control" id="city" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                <div class="invalid-feedback">{{ textError('city') }}</div>
            </div>

            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ trans('users.site') }}:</label>
                <input type="text" class="form-control" id="site" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="form-group{{ hasError('created') }}">
                <label for="created">{{ trans('users.registered') }}:</label>
                <input type="text" class="form-control" id="created" name="created" maxlength="10" value="{{ getInput('created', dateFixed($user->created_at, 'd.m.Y')) }}" required>
                <div class="invalid-feedback">{{ textError('created') }}</div>
            </div>

            <div class="form-group{{ hasError('birthday') }}">
                <label for="birthday">{{ trans('users.birthday') }}:</label>
                <input type="text" class="form-control" id="birthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                <div class="invalid-feedback">{{ textError('birthday') }}</div>
            </div>

            <div class="form-group{{ hasError('icq') }}">
                <label for="icq">ICQ:</label>
                <input type="text" class="form-control" id="icq" name="icq" maxlength="10" value="{{ getInput('icq', $user->icq) }}">
                <div class="invalid-feedback">{{ textError('icq') }}</div>
            </div>

            <div class="form-group{{ hasError('skype') }}">
                <label for="skype">Skype:</label>
                <input type="text" class="form-control" id="skype" name="skype" maxlength="31" value="{{ getInput('skype', $user->skype) }}">
                <div class="invalid-feedback">{{ textError('skype') }}</div>
            </div>

            <div class="form-group{{ hasError('point') }}">
                <label for="point">{{ trans('users.assets') }}:</label>
                <input type="text" class="form-control" id="point" name="point" maxlength="10" value="{{ getInput('point', $user->point) }}" required>
                <div class="invalid-feedback">{{ textError('point') }}</div>
            </div>

            <div class="form-group{{ hasError('money') }}">
                <label for="money">{{ trans('users.moneys') }}:</label>
                <input type="text" class="form-control" id="money" name="money" maxlength="15" value="{{ getInput('money', $user->money) }}" required>
                <div class="invalid-feedback">{{ textError('money') }}</div>
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">{{ trans('users.status') }}:</label>
                <input type="text" class="form-control" id="status" name="status" maxlength="25" value="{{ getInput('status', $user->status) }}">
                <div class="invalid-feedback">{{ textError('status') }}</div>
            </div>

            <div class="form-group{{ hasError('posrating') }}">
                <label for="posrating">{{ trans('users.reputation') }} ({{ trans('main.pluses') }}):</label>
                <input type="text" class="form-control" id="posrating" name="posrating" maxlength="10" value="{{ getInput('posrating', $user->posrating) }}" required>
                <div class="invalid-feedback">{{ textError('posrating') }}</div>
            </div>

            <div class="form-group{{ hasError('negrating') }}">
                <label for="negrating">{{ trans('users.reputation') }} ({{ trans('main.minuses') }}):</label>
                <input type="text" class="form-control" id="negrating" name="negrating" maxlength="10" value="{{ getInput('negrating', $user->negrating) }}" required>
                <div class="invalid-feedback">{{ textError('negrating') }}</div>
            </div>

            <?php $inputThemes = getInput('themes', $user->themes); ?>
            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">{{ trans('users.theme') }}:</label>

                <select class="form-control" name="themes" id="themes">
                    <option value="0">{{ trans('main.automatically') }}</option>

                    @foreach ($allThemes as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('themes') }}</div>
            </div>

            <?php $inputGender = getInput('gender', $user->gender); ?>
            {{ trans('users.gender') }}:
            <div class="form-group{{ hasError('gender') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderMale">{{ trans('users.male') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderFemale">{{ trans('users.female') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('gender') }}</div>
            </div>

            <div class="form-group{{ hasError('info') }}">
                <label for="info">{{ trans('users.about') }}:</label>
                <textarea class="form-control markItUp" id="info" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                <div class="invalid-feedback">{{ textError('info') }}</div>
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div><br>

    <div class="b"><b>{{ trans('users.additional_info') }}</b></div>

    @if ($user->level === 'pended')
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ trans('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <div class="p-1 bg-danger text-white">{{ trans('users.user_banned') }}</div>
            {{ trans('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($banhist)
                {{ trans('users.term') }}: {{ formatTime($banhist->term) }}<br>
                {{ trans('users.reason_ban') }}: {!! bbCode($banhist->reason) !!}<br>
                {{ trans('users.banned') }}: {!! $banhist->sendUser->getProfile() !!}<br>
            @endif
        </div>
    @endif

    {{ trans('users.last_visit') }}: {{ dateFixed($user->updated_at, 'j F Y / H:i') }}<br><br>

    @if (! in_array($user->level, $adminGroups, true))
        <i class="fa fa-times"></i> <a href="/admin/users/delete?user={{ $user->login }}">{{ trans('main.delete') }}</a><br>
    @endif
@stop
