@extends('layout')

@section('title', __('admin.paid_adverts.edit_advert'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active"><a href="/admin/paid-adverts">{{ __('index.paid_adverts') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.paid_adverts.edit_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($advert->id)
        @if ($advert->deleted_at > SITETIME)
            <div class="alert alert-success">
                {{ __('admin.paid_adverts.expires') }}: {{ dateFixed($advert->deleted_at) }}
            </div>
        @else
            <div class="alert alert-warning">
                {{ __('admin.paid_adverts.expired') }}: {{ dateFixed($advert->deleted_at) }}
            </div>
        @endif
    @endif

    <div class="section-form mb-3 shadow">
        @include('admin/paid-adverts/_form')
    </div>
@stop
