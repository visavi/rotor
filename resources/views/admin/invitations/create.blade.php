@extends('layout')

@section('title', __('invitations.create_keys'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">{{ __('index.invitations') }}</a></li>
            <li class="breadcrumb-item active">{{ __('invitations.create_keys') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{{ __('admin.invitations.key_generation') }}</h3>
    <div class="section-form mb-3 shadow">
        <form action="/admin/invitations/create" method="post">
            @csrf
            <?php $inputKeys = (int) getInput('keys'); ?>
            <div class="mb-3{{ hasError('keys') }}">
                <label for="keys" class="form-label">{{ __('main.total') }}:</label>
                <select class="form-select" name="keys" id="keys">
                    @foreach ($listKeys as $key)
                        <?php $selected = ($key === $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('keys') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.create') }}</button>
        </form>
    </div>

    <h3>{{ __('admin.invitations.send_to_user') }}</h3>
    <div class="section-form mb-3 shadow">
        <form action="/admin/invitations/send" method="post">
            @csrf
            <div class="mb-3{{ hasError('user') }}">
                <label for="user" class="form-label">{{ __('main.user_login') }}:</label>
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" required>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </div>

            <?php $inputKeys = (int) getInput('userkeys'); ?>
            <div class="mb-3{{ hasError('userkeys') }}">
                <label for="userkeys" class="form-label">{{ __('main.total') }}:</label>

                <select class="form-select" name="userkeys" id="userkeys">

                    @foreach ($listKeys as $key)
                        <?php $selected = ($key === $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('userkeys') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.send') }}</button>
        </form>
    </div>

    @if (isAdmin('boss'))
        <h3>{{ __('admin.invitations.sending_keys') }}</h3>
        <div class="section-form mb-3 shadow">
            {{ __('admin.invitations.send_to_active_users') }}:<br>
            <form action="/admin/invitations/mail" method="post">
                @csrf
                <button class="btn btn-primary">{{ __('main.send') }}</button>
            </form>
        </div>
    @endif
@stop
