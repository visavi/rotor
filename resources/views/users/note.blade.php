@extends('layout')

@section('title')
    {{ __('index.note') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/users/{{ $user->login }}/note" method="post">
            @csrf
            <div class="form-group{{ hasError('notice') }}">
                <label for="notice">{{ __('index.note') }}:</label>
                <textarea class="form-control markItUp" id="notice" rows="5" name="notice" required>{{ getInput('notice', $user->note->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('notice') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div>
@stop
