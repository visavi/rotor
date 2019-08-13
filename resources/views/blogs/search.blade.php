@extends('layout')

@section('title')
    {{ trans('blogs.title_search') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/blogs/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">{{ trans('main.request') }}:</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="{{ trans('main.request') }}" value="{{ getInput('find') }}" required>
                <div class="invalid-feedback">{{ textError('find') }}</div>
            </div>

            {{ trans('main.look_in') }}:
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="form-group{{ hasError('where') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere0">{{ trans('blogs.in_titles') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere1">{{ trans('blogs.in_text') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('where') }}</div>
            </div>

            {{ trans('main.request_type') }}:
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
