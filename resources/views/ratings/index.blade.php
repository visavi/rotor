@extends('layout')

@section('title')
    {{ trans('ratings.title') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ trans('ratings.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post">
            @csrf
            <label for="inputRating">{{ trans('main.rating') }}</label>
            <select class="form-control" id="inputRating" name="vote">
                <?php $selected = ($vote === 'plus') ? ' selected' : ''; ?>
                <option value="plus"{{ $selected }}>{{ trans('main.plus') }}</option>
                <?php $selected = ($vote === 'minus') ? ' selected' : ''; ?>
                <option value="minus"{{ $selected }}>{{ trans('main.minus') }}</option>
            </select>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('main.comment') }}:</label>
                <textarea class="form-control markItUp" id="text" cols="25" rows="5" name="text">{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <button class="btn btn-primary">{{ trans('main.continue') }}</button>
        </form>
    </div><br>

    <i class="fa fa-briefcase"></i> <a href="/ratings/{{ $user->login }}">{{ trans('ratings.history') }}</a><br>
@stop
