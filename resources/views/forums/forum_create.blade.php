@extends('layout')

@section('title')
    {{ trans('forums.title_create') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/forums/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('fid') }}">
                <label for="inputForum">{{ trans('forums.forum') }}</label>
                <select class="form-control" id="inputForum" name="fid">

                    @foreach ($forums as $data)
                        <option value="{{ $data->id }}"{{ $fid === $data->id  && ! $data->closed ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->title }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ $fid === $datasub->id  && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('fid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('forums.topic') }}:</label>
                <input name="title" class="form-control" id="inputTitle" maxlength="50" placeholder="{{ trans('forums.topic') }}" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('forums.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('forumtextlength') }}" data-hint="{{ trans('main.characters_left') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            <?php $checkVote = getInput('vote') ? true : false; ?>
            <?php $checked = $checkVote ? ' checked' : ''; ?>
            <?php $display = $checkVote ? '' : ' style="display: none"'; ?>

            <label>
                <input name="vote" onchange="return showVoteForm();" type="checkbox"{!! $checked !!}> {{ trans('forums.create_vote') }}
            </label><br>

            <div class="js-vote-form"{!! $display !!}>
                <div class="form-group{{ hasError('question') }}">

                    <label for="inputQuestion">{{ trans('forums.question') }}:</label>
                    <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question') }}" maxlength="100">
                    {!! textError('question') !!}
                </div>

                <div class="form-group{{ hasError('answers') }}">

                    <?php $answers = array_diff((array) getInput('answers'), ['']) ?>

                    @for ($i = 0; $i < 10; $i++)
                        <label for="inputAnswers{{ $i }}">{{ trans('forums.answer') }} {{ $i + 1 }}</label>
                        <input type="text" name="answers[]" class="form-control" id="inputAnswers{{ $i }}" value="{{ $answers[$i] ?? '' }}" maxlength="50">
                    @endfor
                    {!! textError('answers') !!}
                </div>
            </div>
            <button class="btn btn-primary">{{ trans('forums.create_topic') }}</button>
        </form>
    </div><br>

    {{ trans('forums.create_rule1') }}<br>
    <a href="/rules">{{ trans('main.rules') }}</a><br>
    {{ trans('forums.create_rule2') }}<br>
    <a href="/forums/search">{{ trans('main.search') }}</a><br>
    {{ trans('forums.create_rule3') }}<br><br>
@stop
