@extends('layout')

@section('title')
    {{ trans('offers.editing_record') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">{{ trans('offers.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('offers.editing_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/offers/edit/{{ $offer->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('type') }}">
                <label for="types">{{ trans('offers.i_want_to') }}</label>

                <?php $inputType = getInput('type', $offer->type); ?>
                <select class="form-control" name="type" id="type">
                    <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>{{ trans('offers.suggest_idea') }}</option>
                    <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>{{ trans('offers.report_problem') }}</option>
                </select>

                {!! textError('type') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('offers.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $offer->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('offers.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $offer->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <button class="btn btn-primary">{{ trans('offers.edit_offer') }}</button>
        </form>
    </div>
@stop
