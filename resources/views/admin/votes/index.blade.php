@extends('layout')

@section('title', __('index.votes'))

@section('header')
    @if (getUser())
        <div class="float-end">
            <a class="btn btn-success" href="{{ route('votes.create') }}">{{ __('main.create') }}</a>
            <a class="btn btn-light" href="{{ route('votes.index', ['page' => $votes->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
        </div>
    @endif

    <h1>{{ __('index.votes') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
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
                    <a href="{{ route('votes.view', ['id' => $vote->id]) }}">{{ $vote->title }}</a>

                    <div class="float-end">
                        <a href="{{ route('admin.votes.edit', ['id' => $vote->id]) }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="{{ route('admin.votes.close', ['id' => $vote->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('votes.confirm_close') }}')" data-bs-toggle="tooltip" title="{{ __('main.close') }}"><i class="fa fa-lock text-muted"></i></a>

                        @if (isAdmin('boss'))
                            <a href="{{ route('admin.votes.delete', ['id' => $vote->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('votes.confirm_delete') }}')" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                </div>

                @if ($vote->description)
                    <div class="section-body mb-3">
                        {{ bbCode($vote->description) }}
                    </div>
                @endif

                <div class="section-body">
                    @if ($vote->topic->id)
                        {{ __('forums.topic') }}: <a href="{{ route('topics.topic', ['id' => $vote->topic->id]) }}">{{ $vote->topic->title }}</a><br>
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

    <div class="mb-3">
        <i class="fa fa-briefcase"></i> <a href="{{ route('admin.votes.history') }}">{{ __('votes.archive_votes') }}</a><br>
    </div>

    @if (isAdmin('boss'))
        <form action="{{ route('admin.votes.restatement') }}" method="post">
            @csrf
            <button class="btn btn-primary">
                <i class="fa fa-sync"></i> {{ __('main.recount') }}
            </button>
        </form>
    @endif
@stop
