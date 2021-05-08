@extends('layout')

@section('title', __('index.my_details'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.my_details') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{{ __('users.change_email') }}</h3>

    <div class="section-form mb-3 shadow">
        <form method="post" action="/accounts/changemail">
            @csrf
            <div class="mb-3{{ hasError('email') }}">
                <label for="email" class="form-label">{{ __('users.email') }}:</label>
                <input class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}">
                <div class="invalid-feedback">{{ textError('email') }}</div>
            </div>

            <div class="mb-3{{ hasError('password') }}">
                <label for="password" class="form-label">{{ __('users.current_password') }}:</label>
                <input class="form-control" type="password" id="password" name="password" maxlength="20">
                <div class="invalid-feedback">{{ textError('password') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>

        <span class="text-muted fst-italic">{{ __('users.email_confirm_condition') }}</span>
    </div>


    <h3>{{ __('users.status_change') }}</h3>

    @if ($user->point >= setting('editstatuspoint'))
        <div class="section-form mb-3 shadow">
            <form method="post" action="/accounts/editstatus">
                @csrf
                <label for="status" class="form-label">{{ __('users.personal_status') }}:</label>
                <div class="input-group{{ hasError('status') }}">
                    <input type="text" class="form-control" id="status" name="status" maxlength="20" value="{{ getInput('status', $user->status) }}">
                    <button class="btn btn-primary">{{ __('main.change') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('status') }}</div>
            </form>

            @if (setting('editstatusmoney'))
                <span class="text-muted fst-italic">{{ __('main.cost') }}: {{ plural(setting('editstatusmoney'), setting('moneyname')) }}</span>
            @endif

        </div>
    @else
        {{ showError(__('users.status_change_condition', ['point' => plural(setting('editstatuspoint'), setting('scorename'))])) }}
    @endif

    <h3>{{ __('users.color_change') }}</h3>

    @if ($user->point >= setting('editcolorpoint'))
        <div class="section-form mb-3 shadow">
            <form method="post" action="/accounts/editcolor">
                @csrf
                <div class="mb-3{{ hasError('color') }}">
                    <label for="color" class="form-label">{{ __('users.personal_color') }}:</label>

                    <div class="input-group colorpick">
                        <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $user->color) }}">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                    </div>

                    <div class="invalid-feedback">{{ textError('color') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.change') }}</button>
            </form>

            @if (setting('editcolormoney'))
                <span class="text-muted fst-italic">{{ __('main.cost') }}: {{ plural(setting('editcolormoney'), setting('moneyname')) }}</span>
            @endif

        </div>
    @else
        {{ showError(__('users.color_change_condition', ['point' => plural(setting('editcolorpoint'), setting('scorename'))])) }}
    @endif

    <h3>{{ __('users.change_password') }}</h3>

    <div class="section-form mb-3 shadow">
        <form method="post" action="/accounts/editpassword">
            @csrf
            <div class="mb-3{{ hasError('newpass') }}">
                <label for="newpass" class="form-label">{{ __('users.new_password') }}:</label>
                <input class="form-control" id="newpass" name="newpass" maxlength="20" value="{{ getInput('newpass') }}">
                <div class="invalid-feedback">{{ textError('newpass') }}</div>
            </div>

            <div class="mb-3{{ hasError('newpass2') }}">
                <label for="newpass2" class="form-label">{{ __('users.confirm_password') }}:</label>
                <input class="form-control" id="newpass2" name="newpass2" maxlength="20" value="{{ getInput('newpass2') }}">
                <div class="invalid-feedback">{{ textError('newpass2') }}</div>
            </div>

            <div class="mb-3{{ hasError('oldpass') }}">
                <label for="oldpass" class="form-label">{{ __('users.current_password') }}:</label>
                <input class="form-control" type="password" id="oldpass" name="oldpass" maxlength="20">
                <div class="invalid-feedback">{{ textError('oldpass') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <h3>{{ __('users.your_token') }}</h3>

    <div class="section-form mb-3 shadow">
        <form method="post" action="/accounts/apikey">
            @csrf
            @if ($user->apikey)
                <div class="mb-3">
                    <label for="apikey" class="form-label">{{ __('users.token') }}:</label>
                    <div class="input-group">
                        <input class="form-control col-sm-4" type="text" id="apikey" name="apikey" value="{{ $user->apikey }}">
                        <div class="input-group-append" onclick="return copyToClipboard(this)" data-bs-toggle="tooltip" title="{{ __('main.copy') }}">
                            <span class="input-group-text"><i class="far fa-clipboard"></i></span>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" name="action" value="change">{{ __('main.change') }}</button>
                <button class="btn btn-danger" name="action" value="delete">{{ __('main.delete') }}</button>
            @else
                <button class="btn btn-primary" name="action" value="create">{{ __('main.create') }}</button>
            @endif
        </form>

        <span class="text-muted fst-italic">
            {{ __('users.token_required') }} <a href="/api">{{ __('users.api_interface') }}</a>
        </span>
    </div>
@stop
