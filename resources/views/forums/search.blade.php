@extends('layout')

@section('title')
    {{ trans('forums.title_search') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/forums/search">
            <input type="hidden" name="fid" value="{{ $fid }}">

            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">{{ trans('main.request') }}:</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="{{ trans('main.request') }}" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">{{ trans('forums.forum') }}</label>
                <?php $inputSection = (int) getInput('section', $fid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">{{ trans('main.not_matter') }}</option>

                    @foreach ($forums as $data)
                        <?php $selected = ($inputSection === $data->id) ? ' selected' : ''; ?>

                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->title }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection === $datasub->id) ? ' selected' : ''; ?>

                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            <div class="form-group{{ hasError('period') }}">
                <label for="inputPeriod">{{ trans('main.period') }}</label>

                <?php $inputPeriod = (int) getInput('period'); ?>
                <select class="form-control" id="inputPeriod" name="period">
                    <option value="0"{{ $inputPeriod === 0 ? ' selected' : '' }}>{{ trans('main.all_time') }}</option>
                    <option value="1"{{ $inputPeriod === 7 ? ' selected' : '' }}>{{ trans('main.last_day') }}</option>
                    <option value="7"{{ $inputPeriod === 7 ? ' selected' : '' }}>{{ trans('main.last_week') }}</option>
                    <option value="30"{{ $inputPeriod ===30 ? ' selected' : '' }}>{{ trans('main.last_month') }}</option>
                    <option value="365"{{ $inputPeriod === 365 ? ' selected' : '' }}>{{ trans('main.last_year') }}</option>
                </select>
                {!! textError('period') !!}
            </div>

            {{ trans('main.look_in') }}:<br>
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere0">{{ trans('forums.in_topics') }}</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere1">{{ trans('forums.in_posts') }}</label>
            </div>

            {{ trans('main.request_type') }}:<br>
            <?php $inputType = (int) getInput('type'); ?>
            <div class="form-group{{ hasError('type') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType0" name="type" value="0"{{ $inputType === 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType0">{{ trans('main.and') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType === 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType1">{{ trans('main.or') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType === 2 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType2">{{ trans('main.full') }}</label>
                </div>
                {!! textError('type') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.search') }}</button>
        </form>
    </div>
@stop
