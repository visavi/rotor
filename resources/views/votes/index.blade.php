@extends('layout')

@section('title')
    {{ trans('index.votes') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/votes/create">{{ trans('main.create') }}</a><br>
        </div><br>
    @endif

    <h1>{{ trans('index.votes') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.votes') }}</li>
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
                    {{ trans('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                {{ trans('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                {{ trans('main.votes') }}: {{ $vote->count }}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('votes.empty_votes')) !!}
    @endif

    <i class="fa fa-briefcase"></i> <a href="/votes/history">{{ trans('votes.archive_votes') }}</a><br>
@stop
