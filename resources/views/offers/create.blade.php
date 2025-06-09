@extends('layout')

@section('title', __('offers.adding_record'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.index') }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.adding_record') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser('point') >= setting('addofferspoint'))
        <div class="section-form mb-3 shadow">
            <form action="{{ route('offers.create') }}" method="post">
                @csrf
                <?php $inputType = getInput('type', $type); ?>
                <div class="mb-3{{ hasError('type') }}">
                    <label for="inputType" class="form-label">{{ __('offers.i_want_to') }}</label>
                    <select class="form-select" id="inputType" name="type">
                        <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>{{ __('offers.suggest_idea') }}</option>
                        <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>{{ __('offers.report_problem') }}</option>
                    </select>
                    <div class="invalid-feedback">{{ textError('type') }}</div>
                </div>

                <div class="mb-3{{ hasError('title') }}">
                    <label for="inputTitle" class="form-label">{{ __('main.title') }}:</label>
                    <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                    <div class="invalid-feedback">{{ textError('title') }}</div>
                </div>

                <div class="mb-3{{ hasError('text') }}">
                    <label for="text" class="form-label">{{ __('main.text') }}:</label>
                    <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                    <div class="invalid-feedback">{{ textError('text') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </form>
        </div>

    @else
        {{ showError(__('offers.condition_add', ['point' => plural(setting('addofferspoint'), setting('scorename'))])) }}
    @endif
@stop
