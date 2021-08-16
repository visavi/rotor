@extends('layout')

@section('title', __('admin.paid_adverts.create_advert'))

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
        @include('admin/user-fields/_form')
    </div>
@stop
