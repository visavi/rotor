@extends('layout')

@section('title', __('loads.edit_down') . ' ' . $down->title)

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="{{ route('downs.view', ['id' => $down->id]) }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('loads.edit_down') . ' ' . $down->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.loads.index') }}">{{ __('index.loads') }}</a></li>

            @foreach ($down->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.loads.load', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ __('loads.edit_down') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $down->active)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ __('loads.pending_down1') }}
        </div>
    @endif

    @if (isAdmin('boss'))
        @if ($down->active)
            <i class="fa fa-pencil-alt"></i>
            <a class="me-3" href="{{ route('admin.downs.publish', ['id' => $down->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('loads.confirm_unpublish_down') }}')">{{ __('main.unpublish') }}</a>
        @else
            <i class="fa fa-pencil-alt"></i>
            <a class="me-3" href="{{ route('admin.downs.publish', ['id' => $down->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('loads.confirm_publish_down') }}')">{{ __('main.publish') }}</a>
        @endif

        <i class="fas fa-times"></i> <a class="me-3" href="{{ route('admin.downs.delete', ['id' => $down->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('loads.confirm_delete_down') }}')">{{ __('main.delete') }}</a>
        <hr>
    @endif

    <div class="section-form mb-3 shadow">
        <div class="mb-3">
            {{ __('main.author') }}: {{ $down->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($down->created_at) }}</small>
        </div>

        @include('loads/_form')
    </div>
@stop
