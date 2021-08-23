@extends('layout')

@section('title', __('index.user_fields'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/admin/user-fields/create">{{ __('main.create') }}</a>
    </div>

    <h1>{{ __('index.user_fields') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.user_fields') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($fields->isNotEmpty())
        @foreach ($fields as $field)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="far fa-list-alt"></i>
                    {{ $field->name }}
                    <div class="float-end">
                        <a href="/admin/user-fields/{{ $field->id }}/edit"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/user-fields/{{ $field->id }}" onclick="return deletePost(this)" data-token="{{ csrf_token() }}"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    <span class="badge {{ $field->type === 'input' ? 'bg-success' : 'bg-primary' }}">Тип:  {{ $field->type }}</span><br>
                    Размер: {{ $field->length }}<br>
                    Обязательное: {{ $field->required }}
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('admin.user_fields.empty_fields')) }}
    @endif
@stop
