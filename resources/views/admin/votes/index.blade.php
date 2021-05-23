@extends('layout')

@section('title', __('index.votes'))

@section('header')
    @if (getUser())
        <div class="float-end">
            <a class="btn btn-success" href="/votes/create">{{ __('main.create') }}</a>
            <a class="btn btn-light" href="/votes?page={{ $votes->currentPage() }}"><i class="fas fa-wrench"></i></a>
        </div>
    @endif

    <h1>{{ __('index.votes') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.votes') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-chart-bar"></i>
                    <a href="/votes/{{ $vote['id'] }}">{{ $vote->title }}</a>

                    <div class="float-end">
                        <a href="/admin/votes/edit/{{ $vote->id }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/votes/close/{{ $vote->id }}?token={{ csrf_token() }}" onclick="return confirm('{{ __('votes.confirm_close') }}')" data-bs-toggle="tooltip" title="{{ __('main.close') }}"><i class="fa fa-lock text-muted"></i></a>

                        @if (isAdmin('boss'))
                            <a href="/admin/votes/delete/{{ $vote->id }}?token={{ csrf_token() }}" onclick="return confirm('{{ __('votes.confirm_delete') }}')" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                </div>

                @if ($vote->description)
                    <div class="section-body border-bottom mb-3">
                        {{ bbCode($vote->description) }}
                    </div>
                @endif

                <div class="section-body">
                    @if ($vote->topic->id)
                        {{ __('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                    @endif

                    {{ __('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                    {{ __('main.votes') }}: {{ $vote->count }}<br>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('votes.empty_votes')) }}
    @endif

    {{ $votes->links() }}

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/votes/restatement?token={{ csrf_token() }}">{{ __('main.recount') }}</a><br>
    @endif

    <i class="fa fa-briefcase"></i> <a href="/admin/votes/history">{{ __('votes.archive_votes') }}</a><br>
@stop
