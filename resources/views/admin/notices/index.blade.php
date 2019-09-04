@extends('layout')

@section('title')
    {{ __('index.email_templates') }}
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/notices/create">{{ __('main.add') }}</a>
    </div><br>

    <h1>{{ __('index.email_templates') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.email_templates') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($notices->isNotEmpty())
        @foreach ($notices as $notice)

            <div class="b">
                <i class="fa fa-envelope"></i> <b><a href="/admin/notices/edit/{{ $notice->id }}">{{ $notice->name }}</a></b>

                <div class="float-right">
                    @if ($notice->protect)
                        <i class="fa fa-lock"></i>
                    @else
                        <a href="/admin/notices/delete/{{ $notice->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.notices.confirm_delete') }}')"><i class="fa fa-trash-alt"></i></a>
                    @endif
                </div>
            </div>

            <div>
                <span class="badge badge-info">{{ __('main.type') }}: {{ $notice->type }}</span><br>
                {{ __('main.changed') }}: {!! $notice->user->getProfile() !!}
                ({{ dateFixed($notice->updated_at) }})
            </div>
        @endforeach

        <br>{{ __('main.total') }}: {{ $notices->count() }}<br><br>

    @else
        {!! showError(__('admin.notices.empty_notices')) !!}
    @endif
@stop
