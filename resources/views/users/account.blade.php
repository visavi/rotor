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
        <form method="post" action="{{ route('accounts.change-mail') }}">
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
        <div class="alert alert-warning">
            <i class="fa-solid fa-circle-exclamation fa-lg"></i>
            {{ __('users.status_change_condition', ['point' => plural(setting('editstatuspoint'), setting('scorename'))]) }}
        </div>
    @endif

    <h3>{{ __('users.color_change') }}</h3>

    @if ($user->point >= setting('editcolorpoint'))
        <div class="section-form mb-3 shadow">
            <form method="post" action="/accounts/editcolor">
                @csrf
                <?php $color = getInput('color', $user->color); ?>
                <div class="col-sm-4 mb-3{{ hasError('color') }}">
                    <label for="color" class="form-label">{{ __('users.personal_color') }}:</label>
                    <div class="input-group">
                        <input type="text" name="color" class="form-control colorpicker" id="color" maxlength="7" value="{{ $color }}">
                        <input type="color" class="form-control form-control-color colorpicker-addon" value="{{ $color }}">
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
        <div class="alert alert-warning">
            <i class="fa-solid fa-circle-exclamation fa-lg"></i>
            {{ __('users.color_change_condition', ['point' => plural(setting('editcolorpoint'), setting('scorename'))]) }}
        </div>
    @endif

    <h3>{{ __('users.change_password') }}</h3>

    <div class="section-form mb-3 shadow">
        <form method="post" action="/accounts/editpassword">
            @csrf
            <div class="mb-3{{ hasError('new_password') }}">
                <label for="new_password" class="form-label">{{ __('users.new_password') }}:</label>
                <input class="form-control" id="new_password" name="new_password" maxlength="20" value="{{ getInput('new_password') }}">
                <div class="invalid-feedback">{{ textError('new_password') }}</div>
            </div>

            <div class="mb-3{{ hasError('confirm_password') }}">
                <label for="confirm_password" class="form-label">{{ __('users.confirm_password') }}:</label>
                <input class="form-control" id="confirm_password" name="confirm_password" maxlength="20" value="{{ getInput('confirm_password') }}">
                <div class="invalid-feedback">{{ textError('confirm_password') }}</div>
            </div>

            <div class="mb-3{{ hasError('old_password') }}">
                <label for="old_password" class="form-label">{{ __('users.current_password') }}:</label>
                <input class="form-control" type="password" id="old_password" name="old_password" maxlength="20">
                <div class="invalid-feedback">{{ textError('old_password') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <h3>{{ __('users.your_token') }}</h3>

    <div class="section-form mb-3 shadow">
        <form method="post" action="/accounts/apikey">
            @csrf
            @if ($user->apikey)
                <div class="col-sm-4 mb-3">
                    <label for="apikey" class="form-label">{{ __('users.token') }}:</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="apikey" name="apikey" value="{{ $user->apikey }}">
                        <span class="input-group-text" onclick="return copyToClipboard(this)" data-bs-toggle="tooltip" title="{{ __('main.copy') }}"><i class="far fa-clipboard"></i></span>
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
