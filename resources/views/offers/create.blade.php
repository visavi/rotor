@extends('layout')

@section('title')
    {{ __('offers.adding_record') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/offer">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.adding_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser('point') >= setting('addofferspoint'))
        <div class="form">
            <form action="/offers/create" method="post">
                @csrf
                <?php $inputType = getInput('type', $type); ?>
                <div class="form-group{{ hasError('type') }}">
                    <label for="inputType">{{ __('offers.i_want_to') }}</label>
                    <select class="form-control" id="inputType" name="type">
                        <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>{{ __('offers.suggest_idea') }}</option>
                        <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>{{ __('offers.report_problem') }}</option>
                    </select>
                    <div class="invalid-feedback">{{ textError('type') }}</div>
                </div>

                <div class="form-group{{ hasError('title') }}">
                    <label for="inputTitle">{{ __('main.title') }}:</label>
                    <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                    <div class="invalid-feedback">{{ textError('title') }}</div>
                </div>

                <div class="form-group{{ hasError('text') }}">
                    <label for="text">{{ __('main.text') }}:</label>
                    <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                    <div class="invalid-feedback">{{ textError('text') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </form>
        </div><br>

    @else
        {!! showError(__('offers.condition_add', ['point' => plural(setting('addofferspoint'), setting('scorename'))])) !!}
    @endif
@stop
