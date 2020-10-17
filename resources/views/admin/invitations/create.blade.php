@extends('layout')

@section('title', __('admin.invitations.creation_keys'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">{{ __('index.invitations') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.invitations.creation_keys') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{{ __('admin.invitations.key_generation') }}</h3>
    <div class="section-form p-2 shadow">
        <form action="/admin/invitations/create" method="post">
            @csrf
            <?php $inputKeys = (int) getInput('keys'); ?>
            <div class="form-group{{ hasError('keys') }}">
                <label for="keys">{{ __('main.total') }}:</label>
                <select class="form-control" name="keys" id="keys">
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
    <div class="section-form p-2 shadow">
        <form action="/admin/invitations/send" method="post">
            @csrf
            <div class="form-group{{ hasError('user') }}">
                <label for="user">{{ __('main.user_login') }}:</label>
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" required>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </div>

            <?php $inputKeys = (int) getInput('userkeys'); ?>
            <div class="form-group{{ hasError('userkeys') }}">
                <label for="userkeys">{{ __('main.total') }}:</label>

                <select class="form-control" name="userkeys" id="userkeys">

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
        <div class="section-form p-2 shadow">
            {{ __('admin.invitations.send_to_active_users') }}:<br>
            <form action="/admin/invitations/mail" method="post">
                @csrf
                <button class="btn btn-primary">{{ __('main.send') }}</button>
            </form>
        </div>
    @endif
@stop
