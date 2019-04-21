@extends('layout')

@section('title')
    {{ trans('users.note') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ trans('users.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/users/{{ $user->login }}/note" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('notice') }}">
                <label for="notice">{{ trans('users.note') }}:</label>
                <textarea class="form-control markItUp" id="notice" rows="5" name="notice" required>{{ getInput('notice', $user->note->text) }}</textarea>
                {!! textError('notice') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.save') }}</button>
        </form>
    </div>
@stop
