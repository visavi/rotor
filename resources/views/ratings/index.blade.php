@extends('layout')

@section('title', __('index.reputation_edit') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.reputation_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post">
            @csrf
            <label for="inputRating" class="form-label">{{ __('main.rating') }}</label>
            <select class="form-select" id="inputRating" name="vote">
                <?php $selected = ($vote === 'plus') ? ' selected' : ''; ?>
                <option value="plus"{{ $selected }}>{{ __('main.plus') }}</option>
                <?php $selected = ($vote === 'minus') ? ' selected' : ''; ?>
                <option value="minus"{{ $selected }}>{{ __('main.minus') }}</option>
            </select>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('main.comment') }}:</label>
                <textarea class="form-control markItUp" id="text" cols="25" rows="5" name="text">{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.continue') }}</button>
        </form>
    </div>

    <i class="fa fa-briefcase"></i> <a href="/ratings/{{ $user->login }}">{{ __('ratings.history') }}</a><br>
@stop
