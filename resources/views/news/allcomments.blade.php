@extends('layout')

@section('title', __('main.last_comments'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.last_comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <i class="fa fa-comment fa-lg text-muted"></i>
                        <b><a href="/news/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b>
                        <span class="badge badge-light">{{ $data->count_comments }}</span>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($data->text) }}
                    </div>

                    {{ __('main.posted') }}: {{ $data->user->getProfile() }}
                    <small class="section-date text-muted font-italic">{{ dateFixed($data->created_at) }}</small>

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">{{ $data->brow }}, {{ $data->ip }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('main.empty_comments')) }}
    @endif

    {{ $comments->links() }}
@stop
