@extends('layout')

@section('title', __('admin.modules.upload'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.modules.index') }}">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.modules.upload') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('admin/modules/_tabs')

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fas fa-file-archive"></i> {{ __('admin.modules.upload_from_file') }}
        </div>
        <div class="section-content">
            <form action="{{ route('admin.modules.upload.zip') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('admin.modules.zip_file') }}:</label>
                    <input type="file" class="form-control" name="zip" accept=".zip" required>
                </div>
                <button class="btn btn-primary"><i class="fas fa-upload"></i> {{ __('admin.modules.upload') }}</button>
            </form>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fas fa-link"></i> {{ __('admin.modules.upload_from_url') }}
        </div>
        <div class="section-content">
            <form action="{{ route('admin.modules.download') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label class="form-label">URL {{ __('admin.modules.zip_file') }}:</label>
                    <input type="url" class="form-control" name="url" placeholder="https://github.com/.../MyModule.zip" required>
                </div>
                <button class="btn btn-primary"><i class="fas fa-download"></i> {{ __('admin.modules.download') }}</button>
            </form>
        </div>
    </div>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ __('admin.modules.upload_warning') }}
    </div>

    <div class="section shadow">
        <div class="section-title">{{ __('admin.modules.zip_structure') }}</div>
        <div class="section-content">
            <pre class="mb-0">MyModule.zip
└── MyModule/
    ├── module.php
    ├── routes.php
    ├── hooks.php
    └── ...</pre>
        </div>
    </div>
@stop
