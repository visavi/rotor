@extends('layout')

@section('title', __('offers.editing_record'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.index', ['type' => $offer->type]) }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.editing_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="{{ route('offers.edit', ['id' => $offer->id]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('type') }}">
                <label for="types" class="form-label">{{ __('offers.i_want_to') }}</label>

                <?php $inputType = getInput('type', $offer->type); ?>
                <select class="form-select" name="type" id="type">
                    <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>{{ __('offers.suggest_idea') }}</option>
                    <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>{{ __('offers.report_problem') }}</option>
                </select>

                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('main.title') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="{{ setting('offer_title_max') }}" value="{{ getInput('title', $offer->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('offer_text_max') }}" id="text" rows="5" name="text" required>{{ getInput('text', $offer->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
