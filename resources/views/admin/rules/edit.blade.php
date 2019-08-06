@extends('layout')

@section('title')
    {{ trans('admin.rules.editing_rules') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/rules">{{ trans('index.site_rules') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.rules.editing_rules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/rules/edit" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('main.text') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="25" name="msg" required>{{ getInput('msg', $rules->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>
            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div><br>

    <b>{{ trans('admin.rules.variables') }}:</b><br>

    %SITENAME% - {{ trans('admin.rules.sitename') }}<br><br>
@stop
