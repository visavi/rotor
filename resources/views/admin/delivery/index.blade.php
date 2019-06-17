@extends('layout')

@section('title')
    {{ trans('index.private_mailing') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.private_mailing') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/delivery" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('main.message') }}:</label>
                <textarea rows="5" class="form-control markItUp" id="msg" name="msg" required>{{ getInput('msg') }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            {{ trans('main.send') }}:<br>
            <?php $inputType = (int) getInput('type', 1); ?>

            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType1">{{ trans('admin.delivery.online') }}</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType === 2 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType2">{{ trans('admin.delivery.active') }}</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType3" name="type" value="3"{{ $inputType === 3 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType3">{{ trans('admin.delivery.admins') }}</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputType4" name="type" value="4"{{ $inputType === 4 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputType4">{{ trans('admin.delivery.users') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.send') }}</button>
        </form>
    </div>
@stop
