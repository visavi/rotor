@extends('layout')

@section('title', __('offers.editing_record'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.editing_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/offers/edit/{{ $offer->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('type') }}">
                <label for="type">{{ __('offers.i_want_to') }}</label>

                <?php $inputType = getInput('type', $offer->type); ?>
                <select class="form-control" name="type" id="type">
                    <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>{{ __('offers.suggest_idea') }}</option>
                    <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>{{ __('offers.report_problem') }}</option>
                </select>

                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ __('main.title') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $offer->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $offer->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $offer->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close_comments') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
