@extends('layout')

@section('title')
    {{ __('votes.edit_vote') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item"><a href="/votes/{{ $vote->id }}">{{ $vote->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.edit_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-2 shadow">
        <form action="/admin/votes/edit/{{ $vote->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title"><span class="text-success">{{ __('votes.question') }}:</span></label>
                <input class="form-control" name="title" id="title" maxlength="100" value="{{ getInput('title', $vote->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('answers') }}">
                <?php $answers = getInput('answers', $vote->getAnswers); ?>
                <?php $answers = array_slice($answers + array_fill(0, 10, ''), 0, 10, true); ?>

                @foreach ($answers as $key => $answer)
                    <label for="inputAnswers{{ $key }}">
                        @if (isset($vote->getAnswers[$key]))
                            <span class="text-success">{{ __('votes.answer') }} {{ $loop->iteration }}:</span>
                        @else
                            {{ __('votes.answer') }} {{ $loop->iteration }}:
                        @endif
                    </label>
                   <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                @endforeach
                <div class="invalid-feedback">{{ textError('answers') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <p class="text-muted font-italic">{{ __('votes.hint_text') }}</p>
@stop
