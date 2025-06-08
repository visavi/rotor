@extends('layout')

@section('title', __('admin.notices.create'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/notices">{{ __('index.email_templates') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.notices.create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/notices/create" method="post">
            @csrf
            <div class="mb-3{{ hasError('type') }}">
                <label for="type" class="form-label">{{ __('main.type') }} (a-z0-9_-):</label>
                <input type="text" class="form-control" id="type" name="type" maxlength="20" value="{{ getInput('type') }}" required>
                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('main.title') }}:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="15" name="text" required>{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input name="protect" class="form-check-input" type="checkbox" value="1"{{ getInput('protect') ? ' checked' : '' }}>
                    {{ __('admin.notices.system_template') }}
                </label>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div>
@stop
