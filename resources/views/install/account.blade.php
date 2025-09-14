@extends('layout_simple')

@section('title', __('install.step5_install'))

@section('content')
    <div class="container border px-5">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        <h1>{{ __('install.step5_install') }}</h1>

        {{ __('install.create_admin_info') }}<br>
        {{ __('install.create_admin_errors') }}<br>
        {{ __('install.delete_install') }}<br><br>

        <div class="section-form mb-3 shadow">
            <form method="post" action="/install/account?lang={{ $lang }}">
                <div class="mb-3{{ hasError('login') }}">
                    <label for="login" class="form-label">{{ __('users.login') }} (max20):</label>
                    <input type="text" class="form-control" name="login" id="login" maxlength="20" value="{{ $login }}">
                    <span class="text-muted fst-italic">{{ __('users.login_requirements') }}</span>
                    <div class="invalid-feedback">{{ textError('login') }}</div>
                </div>
                <div class="mb-3{{ hasError('password') }}">
                    <label for="password" class="form-label">{{ __('users.password') }} (max20):</label>
                    <input class="form-control" name="password" id="password" type="password" maxlength="50">
                    <div class="invalid-feedback">{{ textError('password') }}</div>
                </div>
                <div class="mb-3{{ hasError('password2') }}">
                    <label for="password2" class="form-label">{{ __('users.confirm_password') }}:</label>
                    <input class="form-control" name="password2" id="password2" type="password" maxlength="50">
                    <div class="invalid-feedback">{{ textError('password2') }}</div>
                </div>
                <div class="mb-3{{ hasError('email') }}">
                    <label for="email" class="form-label">{{ __('users.email') }}:</label>
                    <input class="form-control" name="email" id="email" maxlength="50" value="{{ $email }}">
                    <div class="invalid-feedback">{{ textError('email') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.create') }}</button>
            </form>
        </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
