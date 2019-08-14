@extends('layout')

@section('title')
    {{ trans('loads.search') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/loads/search">
            <input type="hidden" name="cid" value="{{ $cid }}">

            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">{{ trans('main.request') }}</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="{{ trans('main.request') }}" value="{{ getInput('find') }}" required>
                <div class="invalid-feedback">{{ textError('find') }}</div>
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">{{ trans('loads.load') }}:</label>
                <?php $inputSection = (int) getInput('section', $cid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">{{ trans('main.not_matter') }}</option>

                    @foreach ($categories as $data)
                        <?php $selected = ($inputSection === $data->id) ? ' selected' : ''; ?>
                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->name }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection === $datasub->id) ? ' selected' : ''; ?>
                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('section') }}</div>
            </div>

            {{ trans('main.look_in') }}:<br>
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere0">{{ trans('loads.in_title') }}</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere1">{{ trans('loads.in_text') }}</label>
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
                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>
            <button class="btn btn-primary">{{ trans('main.search') }}</button>
        </form>
    </div>
@stop
