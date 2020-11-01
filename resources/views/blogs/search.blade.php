@extends('layout')

@section('title', __('blogs.title_search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-3 shadow">
        <form action="/blogs/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">{{ __('main.request') }}:</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="{{ __('main.request') }}" value="{{ getInput('find') }}" required>
                <div class="invalid-feedback">{{ textError('find') }}</div>
            </div>

            {{ __('main.look_in') }}:
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="form-group{{ hasError('where') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere0">{{ __('blogs.in_titles') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputWhere1">{{ __('blogs.in_text') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('where') }}</div>
            </div>

            {{ __('main.request_type') }}:
            <?php $inputType = (int) getInput('type'); ?>
            <div class="form-group{{ hasError('type') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType0" name="type" value="0"{{ $inputType === 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType0">{{ __('main.and') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType === 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType1">{{ __('main.or') }}</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType === 2 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType2">{{ __('main.full') }}</label>
                </div>
                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.search') }}</button>
        </form>
    </div>
@stop
