@extends('layout')

@section('title', __('index.votes') . ' (' . __('main.page_num', ['page' => $votes->currentPage()]) . ')')

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/votes/create">{{ __('main.create') }}</a><br>
        </div><br>
    @endif

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
            <div class="b">
                <i class="fa fa-chart-bar"></i>
                <b><a href="/votes/{{ $vote['id'] }}">{{ $vote->title }}</a></b>
            </div>
            <div>
                @if ($vote->topic->id)
                    {{ __('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                {{ __('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                {{ __('main.votes') }}: {{ $vote->count }}<br>
            </div>
        @endforeach
    @else
        {!! showError(__('votes.empty_votes')) !!}
    @endif

    {{ $votes->links() }}

    <i class="fa fa-briefcase"></i> <a href="/votes/history">{{ __('votes.archive_votes') }}</a><br>
@stop
