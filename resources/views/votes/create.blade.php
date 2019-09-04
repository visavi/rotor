@extends('layout')

@section('title')
    {{ __('votes.create_vote') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.create_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/votes/create" method="post">
            @csrf
            <div class="form-group{{ hasError('question') }}">

                <label for="inputQuestion">{{ __('votes.question') }}:</label>
                <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question') }}" maxlength="100">
                <div class="invalid-feedback">{{ textError('question') }}</div>
            </div>

            <div class="form-group{{ hasError('answer') }}">

                <?php $answers = array_diff((array) getInput('answer'), ['']) ?>

                @for ($i = 0; $i < 10; $i++)
                    <label for="inputAnswer{{ $i }}">{{ __('votes.answer') }} {{ $i + 1 }}</label>
                    <input type="text" name="answer[]" class="form-control" id="inputAnswer{{ $i }}" value="{{ $answers[$i] ?? '' }}" maxlength="50">
                @endfor
                <div class="invalid-feedback">{{ textError('answer') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.create') }}</button>
        </form>
    </div>
@stop
