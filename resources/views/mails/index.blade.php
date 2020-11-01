@extends('layout')

@section('title', __('index.mails'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.mails') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-3 shadow">
        <form method="post" action="/mails">

            @if (! getUser())
                <div class="form-group{{ hasError('name') }}">
                    <label for="inputName">{{ __('mails.name') }}:</label>
                    <input type="text" class="form-control" id="inputName" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                    <div class="invalid-feedback">{{ textError('name') }}</div>
                </div>
            @endif

            @if (empty(getUser('email')))
                <div class="form-group{{ hasError('email') }}">
                    <label for="inputEmail">{{ __('mails.email') }}:</label>
                    <input type="text" class="form-control" id="inputEmail" name="email" maxlength="50" value="{{ getInput('email') }}" required>
                    <div class="invalid-feedback">{{ textError('email') }}</div>
                </div>
            @endif

            <div class="form-group{{ hasError('message') }}">
                <label for="message">{{ __('mails.message') }}:</label>
                <textarea class="form-control markItUp" id="message" rows="5" name="message" required>{{ getInput('message') }}</textarea>
                <div class="invalid-feedback">{{ textError('message') }}</div>
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">{{ __('main.send') }}</button>
        </form>
    </div>
@stop
