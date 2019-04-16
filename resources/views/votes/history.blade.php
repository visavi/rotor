@extends('layout')

@section('title')
    {{ trans('votes.archive_votes') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ trans('votes.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('votes.archive_votes') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-briefcase"></i>
                <b><a href="/votes/history/{{ $vote->id }}">{{ $vote->title }}</a></b>
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
@stop
