@extends('layout')

@section('title')
    {{ trans('votes.create_vote') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ trans('votes.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('votes.create_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/votes/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('question') }}">

                <label for="inputQuestion">{{ trans('votes.question') }}:</label>
                <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question') }}" maxlength="100">
                {!! textError('question') !!}
            </div>

            <div class="form-group{{ hasError('answer') }}">

                <?php $answers = array_diff((array) getInput('answer'), ['']) ?>

                @for ($i = 0; $i < 10; $i++)
                    <label for="inputAnswer{{ $i }}">{{ trans('votes.answer') }} {{ $i + 1 }}</label>
                    <input type="text" name="answer[]" class="form-control" id="inputAnswer{{ $i }}" value="{{ $answers[$i] ?? '' }}" maxlength="50">
                @endfor
                {!! textError('answer') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.create') }}</button>
        </form>
    </div>
@stop
