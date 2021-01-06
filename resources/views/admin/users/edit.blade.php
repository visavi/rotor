@extends('layout')

@section('title', __('users.edit_user') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/users">{{ __('index.users') }}</a></li>
            <li class="breadcrumb-item active">{{ __('users.edit_user') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getProfile() !!} {{ $user->login }} #{{ $user->id }}</h3>

    @if (getUser('id') === $user->id)
        <div class="p-1 my-1 bg-danger text-white">{{ __('users.edit_user_notice') }}</div>
    @endif

    <div class="section-form mb-3 shadow">
        <form method="post" action="/admin/users/edit?user={{ $user->login }}">
            @csrf
            <?php $inputLevel = getInput('level', $user->level); ?>
            <div class="form-group">
                <label for="level">{{ __('users.position') }}:</label>
                <select class="form-control" id="level" name="level">
                    @foreach ($allGroups as $key => $level)
                        <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('level') }}</div>
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="password">{{ __('users.new_password') }}:</label>
                <input type="text" class="form-control" id="password" name="password" maxlength="50" value="{{ getInput('password') }}">
                <div class="invalid-feedback">{{ textError('password') }}</div>
                <span class="text-muted font-italic">{{ __('users.password_hint') }}</span>
            </div>

            <div class="form-group{{ hasError('email') }}">
                <label for="email">{{ __('users.email') }}:</label>
                <input type="text" class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                <div class="invalid-feedback">{{ textError('email') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('users.name') }}:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="20" value="{{ getInput('name', $user->name) }}">
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>


            <div class="form-group{{ hasError('country') }}">
                <label for="country">{{ __('users.country') }}:</label>
                <input type="text" class="form-control" id="country" name="country" maxlength="30" value="{{ getInput('country', $user->country) }}">
                <div class="invalid-feedback">{{ textError('country') }}</div>
            </div>

            <div class="form-group{{ hasError('city') }}">
                <label for="city">{{ __('users.city') }}:</label>
                <input type="text" class="form-control" id="city" name="city" maxlength="50" value="{{ getInput('city', $user->city) }}">
                <div class="invalid-feedback">{{ textError('city') }}</div>
            </div>

            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ __('users.site') }}:</label>
                <input type="text" class="form-control" id="site" name="site" maxlength="50" value="{{ getInput('site', $user->site) }}">
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="form-group{{ hasError('created') }}">
                <label for="created">{{ __('users.registered') }}:</label>
                <input type="text" class="form-control" id="created" name="created" maxlength="10" value="{{ getInput('created', dateFixed($user->created_at, 'd.m.Y', true)) }}" required>
                <div class="invalid-feedback">{{ textError('created') }}</div>
            </div>

            <div class="form-group{{ hasError('birthday') }}">
                <label for="birthday">{{ __('users.birthday') }}:</label>
                <input type="text" class="form-control" id="birthday" name="birthday" maxlength="10" value="{{ getInput('birthday', $user->birthday) }}">
                <div class="invalid-feedback">{{ textError('birthday') }}</div>
            </div>

            <div class="form-group{{ hasError('point') }}">
                <label for="point">{{ __('users.assets') }}:</label>
                <input type="text" class="form-control" id="point" name="point" maxlength="10" value="{{ getInput('point', $user->point) }}" required>
                <div class="invalid-feedback">{{ textError('point') }}</div>
            </div>

            <div class="form-group{{ hasError('money') }}">
                <label for="money">{{ __('users.moneys') }}:</label>
                <input type="text" class="form-control" id="money" name="money" maxlength="15" value="{{ getInput('money', $user->money) }}" required>
                <div class="invalid-feedback">{{ textError('money') }}</div>
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">{{ __('users.status') }}:</label>
                <input type="text" class="form-control" id="status" name="status" maxlength="25" value="{{ getInput('status', $user->status) }}">
                <div class="invalid-feedback">{{ textError('status') }}</div>
            </div>

            <div class="form-group{{ hasError('posrating') }}">
                <label for="posrating">{{ __('users.reputation') }} ({{ __('main.pluses') }}):</label>
                <input type="text" class="form-control" id="posrating" name="posrating" maxlength="10" value="{{ getInput('posrating', $user->posrating) }}" required>
                <div class="invalid-feedback">{{ textError('posrating') }}</div>
            </div>

            <div class="form-group{{ hasError('negrating') }}">
                <label for="negrating">{{ __('users.reputation') }} ({{ __('main.minuses') }}):</label>
                <input type="text" class="form-control" id="negrating" name="negrating" maxlength="10" value="{{ getInput('negrating', $user->negrating) }}" required>
                <div class="invalid-feedback">{{ textError('negrating') }}</div>
            </div>

            <?php $inputThemes = getInput('themes', $user->themes); ?>
            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">{{ __('users.theme') }}:</label>

                <select class="form-control" name="themes" id="themes">
                    @foreach ($allThemes as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('themes') }}</div>
            </div>

            <?php $inputGender = getInput('gender', $user->gender); ?>
            {{ __('users.gender') }}:
            <div class="form-group{{ hasError('gender') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderMale">{{ __('main.male') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderFemale">{{ __('main.female') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('gender') }}</div>
            </div>

            <div class="form-group{{ hasError('info') }}">
                <label for="info">{{ __('users.about') }}:</label>
                <textarea class="form-control markItUp" id="info" rows="5" name="info">{{ getInput('info', $user->info) }}</textarea>
                <div class="invalid-feedback">{{ textError('info') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <div class="b"><b>{{ __('users.additional_info') }}</b></div>

    @if ($user->level === 'pended')
        <div class="p-1 my-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="section-form mb-3 shadow">
            <div class="p-1 my-1 bg-danger text-white">{{ __('users.user_banned') }}</div>
            {{ __('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($banhist)
                {{ __('users.term') }}: {{ formatTime($banhist->term) }}<br>
                {{ __('users.reason_ban') }}: {!! bbCode($banhist->reason) !!}<br>
                {{ __('users.banned') }}: {!! $banhist->sendUser->getProfile() !!}<br>
            @endif
        </div>
    @endif

    {{ __('users.last_visit') }}: {{ dateFixed($user->updated_at, 'j F Y / H:i') }}<br><br>

    @if (! in_array($user->level, $adminGroups, true))
        <i class="fa fa-times"></i> <a href="/admin/users/delete?user={{ $user->login }}">{{ __('main.delete') }}</a><br>
    @endif
@stop
