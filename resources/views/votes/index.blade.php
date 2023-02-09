@extends('layout')

@section('title', __('index.votes') . ' (' . __('main.page_num', ['page' => $votes->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        @if (getUser())
            <a class="btn btn-success" href="/votes/create">{{ __('main.create') }}</a>
        @endif

        @if (isAdmin('moder'))
            <a class="btn btn-light" href="/admin/votes?page={{ $votes->currentPage() }}"><i class="fas fa-wrench"></i></a>
        @endif
    </div>

    <h1>{{ __('index.votes') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
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
                </div>

                @if ($vote->description)
                    <div class="section-body mb-3">
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

    <i class="fa fa-briefcase"></i> <a href="/votes/history">{{ __('votes.archive_votes') }}</a><br>
@stop
