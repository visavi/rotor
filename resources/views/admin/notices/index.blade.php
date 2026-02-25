@extends('layout')

@section('title', __('index.email_templates'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/admin/notices/create">{{ __('main.add') }}</a>
    </div>

    <h1>{{ __('index.email_templates') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.email_templates') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($notices->isNotEmpty())
        @foreach ($notices as $notice)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-envelope"></i>
                        <a href="/admin/notices/edit/{{ $notice->id }}">{{ $notice->name }}</a>

                    <div class="float-end">
                        @if ($notice->protect)
                            <i class="fa fa-lock"></i>
                        @else
                            <form action="/admin/notices/delete/{{ $notice->id }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('admin.notices.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link p-0"><i class="fa fa-trash-alt"></i></button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    <span class="badge bg-info">{{ __('main.type') }}: {{ $notice->type }}</span><br>
                    {{ __('main.changed') }}: {{ $notice->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($notice->updated_at) }}</small>
                </div>
            </div>
        @endforeach

        {{ __('main.total') }}: {{ $notices->count() }}<br>
    @else
        {{ showError(__('admin.notices.empty_notices')) }}
    @endif
@stop
