@extends('layout')

@section('title', __('admin.user_fields.create_field'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active"><a href="/admin/user-fields">{{ __('index.user_fields') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.user_fields.create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/user-fields" method="post">
            @csrf
            @include('admin/user-fields/_form')
        </form>
    </div>
@stop
