@extends('layout')

@section('title')
    {{ trans('forums.title_edit_topic') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_edit_topic') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/topics/edit/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('forums.topic') }}:</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="{{ trans('forums.topic') }}" value="{{ getInput('title', $topic->title) }}" required>
                {!! textError('title') !!}
            </div>

            @if ($post)
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ trans('forums.post') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                    <span class="js-textarea-counter"></span>
                    {!! textError('msg') !!}
                </div>
            @endif

            @if ($vote)
                <div class="form-group{{ hasError('question') }}">
                    <label for="question"><span class="text-success">{{ trans('forums.question') }}:</span></label>
                    <input class="form-control" name="question" id="question" maxlength="100" value="{{ getInput('question', $vote->title) }}" required>
                    {!! textError('question') !!}
                </div>

                @if (! $vote->count)
                    <div class="form-group{{ hasError('answers') }}">
                        <?php $answers = getInput('answers', $vote->getAnswers); ?>
                        <?php $answers = array_slice($answers + array_fill(0, 10, ''), 0, 10, true); ?>

                        @foreach ($answers as $key => $answer)
                            <label for="inputAnswers{{ $key }}">
                                @if (isset($vote->getAnswers[$key]))
                                    <span class="text-success">{{ trans('forums.answer') }} {{ $loop->iteration }}:</span>
                                @else
                                    {{ trans('forums.answer') }} {{ $loop->iteration }}:
                                @endif
                            </label>
                            <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                        @endforeach
                        {!! textError('answers') !!}
                    </div>
                @endif
            @endif

            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
