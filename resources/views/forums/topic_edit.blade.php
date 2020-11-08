@extends('layout')

@section('title', __('forums.title_edit_topic'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_topic') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->getName() }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="section-form shadow">
        <form action="/topics/edit/{{ $topic->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ __('forums.topic') }}:</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="{{ __('forums.topic') }}" value="{{ getInput('title', $topic->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            @if ($post)
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('forums.post') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>
            @endif

            @if ($vote)
                <div class="form-group{{ hasError('question') }}">
                    <label for="question"><span class="text-success">{{ __('forums.question') }}:</span></label>
                    <input class="form-control" name="question" id="question" maxlength="100" value="{{ getInput('question', $vote->title) }}" required>
                    <div class="invalid-feedback">{{ textError('question') }}</div>
                </div>

                @if (! $vote->count)
                    <div class="form-group{{ hasError('answers') }}">
                        <?php $answers = getInput('answers', $vote->getAnswers); ?>
                        <?php $answers = array_slice($answers + array_fill(0, 10, ''), 0, 10, true); ?>

                        @foreach ($answers as $key => $answer)
                            <label for="inputAnswers{{ $key }}">
                                @if (isset($vote->getAnswers[$key]))
                                    <span class="text-success">{{ __('forums.answer') }} {{ $loop->iteration }}:</span>
                                @else
                                    {{ __('forums.answer') }} {{ $loop->iteration }}:
                                @endif
                            </label>
                            <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                        @endforeach
                        <div class="invalid-feedback">{{ textError('answers') }}</div>
                    </div>
                @endif
            @endif

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
