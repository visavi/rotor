@extends('layout')

@section('title')
    {{ trans('votes.edit_vote') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/votes">{{ trans('votes.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/votes/{{ $vote->id }}">{{ $vote->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('votes.edit_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/votes/edit/{{ $vote->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title"><span class="text-success">{{ trans('votes.question') }}:</span></label>
                <input class="form-control" name="title" id="title" maxlength="100" value="{{ getInput('title', $vote->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('answers') }}">
                <?php $answers = getInput('answers', $vote->getAnswers); ?>
                <?php $answers = array_slice($answers + array_fill(0, 10, ''), 0, 10, true); ?>

                @foreach ($answers as $key => $answer)
                    <label for="inputAnswers{{ $key }}">
                        @if (isset($vote->getAnswers[$key]))
                            <span class="text-success">{{ trans('votes.answer') }} {{ $loop->iteration }}:</span>
                        @else
                            {{ trans('votes.answer') }} {{ $loop->iteration }}:
                        @endif
                    </label>
                   <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                @endforeach
                {!! textError('answers') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">{{ trans('votes.hint_text') }}</p>
@stop
