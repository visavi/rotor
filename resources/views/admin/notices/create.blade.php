@extends('layout')

@section('title')
    {{ trans('admin.notices.create') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/notices">{{ trans('index.email_templates') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.notices.create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/notices/create" method="post">
            @csrf
            <div class="form-group{{ hasError('type') }}">
                <label for="type">{{ trans('main.type') }} (a-z0-9_-):</label>
                <input type="text" class="form-control" id="type" name="type" maxlength="20" value="{{ getInput('type') }}" required>
                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ trans('main.title') }}:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('main.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="15" name="text" required>{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input name="protect" class="form-check-input" type="checkbox" value="1"{{ getInput('protect') ? ' checked' : '' }}>
                    {{ trans('admin.notices.system_template') }}
                </label>
            </div>

            <button class="btn btn-primary">{{ trans('main.save') }}</button>
        </form>
    </div>
@stop
