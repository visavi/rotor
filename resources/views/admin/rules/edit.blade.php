@extends('layout')

@section('title', __('admin.rules.editing_rules'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/rules">{{ __('index.site_rules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.rules.editing_rules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form shadow">
        <form action="/admin/rules/edit" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="25" name="msg" required>{{ getInput('msg', $rules->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>
            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>

    <b>{{ __('admin.rules.variables') }}:</b><br>

    %SITENAME% - {{ __('admin.rules.sitename') }}<br><br>
@stop
