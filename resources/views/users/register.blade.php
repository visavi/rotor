@extends('layout')

@section('title', __('index.register'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.register') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (setting('regkeys'))
        <div class="alert alert-danger">
            <i class="fa fa-pencil-alt text-muted"></i>
            <b>{{ __('users.confirm_registration') }}</b><br>
            {{ __('users.confirm_registration_hint') }}
        </div>
    @endif

    @if (setting('invite'))
        <div class="alert alert-danger">
            <i class="fa fa-pencil-alt text-muted"></i>
            <b>{{ __('users.invite_registration') }}</b><br>
            {{ __('users.invite_registration_hint') }}
        </div>
    @endif

    @if (isset($_SESSION['social']))
        <div class="bg-success text-light p-1">
            <img class="rounded-circle border" alt="photo" src="{{ $_SESSION['social']->photo }}" style="width: 48px; height: 48px;">
            <span class="badge bg-primary">{{ $_SESSION['social']->network }}</span> {{ $_SESSION['social']->first_name }} {{ $_SESSION['social']->last_name }} {{ isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : '' }}
        </div>
        <div class="bg-info text-white p-1 mb-3">
            {!! __('users.social_auth_text') !!}
        </div>
    @endif

    <script src="//ulogin.ru/js/ulogin.js" async defer></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,google,yandex,instagram;redirect_uri={{ siteUrl() }}/register;mobilebuttons=0;">
    </div>

    <div class="section-form mb-3 shadow">
        <form action="/register" method="post">

            <div class="mb-3{{ hasError('login') }}">
                <label for="inputLogin" class="form-label">{{ __('users.login') }}:</label>
                <input onkeyup="return checkLogin(this);" class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ getInput('login') }}" required>
                <div class="invalid-feedback">{{ textError('login') }}</div>
                <span class="text-muted font-italic">{{ __('users.login_requirements') }}</span>
            </div>

            <div class="mb-3{{ hasError('password') }}">
                <label for="inputPassword" class="form-label">{{ __('users.password') }}:</label>
                <input class="form-control" name="password" type="password" id="inputPassword" maxlength="20" required>
                <div class="invalid-feedback">{{ textError('password') }}</div>
                <span class="text-muted font-italic">{{ __('users.password_requirements') }}</span>
            </div>

            <div class="mb-3{{ hasError('password2') }}">
                <label for="inputPassword2" class="form-label">{{ __('users.confirm_password') }}:</label>
                <input class="form-control" name="password2" type="password" id="inputPassword2" maxlength="20" required>
                <div class="invalid-feedback">{{ textError('password2') }}</div>
            </div>

            <div class="mb-3{{ hasError('email') }}">
                <label for="inputEmail" class="form-label">{{ __('users.email') }}:</label>
                <input class="form-control" name="email" id="inputEmail" maxlength="50" value="{{ getInput('email') }}" required>
                <div class="invalid-feedback">{{ textError('email') }}</div>
            </div>

            @if (setting('invite'))
                <div class="mb-3{{ hasError('invite') }}">
                    <label for="inputInvite" class="form-label">{{ __('users.invitation_key') }}:</label>
                    <input class="form-control" name="invite" id="inputInvite" maxlength="32" value="{{ getInput('invite') }}" required>
                    <div class="invalid-feedback">{{ textError('invite') }}</div>
                </div>
            @endif

            <?php $inputGender = getInput('gender', 'male'); ?>
            Пол:
            <div class="mb-3{{ hasError('gender') }}">
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender === 'male' ? ' checked' : '' }}>
                    <label class="form-check-label" for="inputGenderMale">{{ __('main.male') }}</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender === 'female' ? ' checked' : '' }}>
                    <label class="form-check-label" for="inputGenderFemale">{{ __('main.female') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('gender') }}</div>
            </div>

            {{ getCaptcha() }}

            <button class="btn btn-primary">{{ __('index.register') }}</button>
        </form>
    </div>

    {!! __('users.register_text') !!}<br>
@stop
