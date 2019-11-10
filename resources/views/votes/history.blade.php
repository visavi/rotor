@extends('layout')

@section('title')
    {{ __('votes.archive_votes') }} ({{ __('main.page_num', ['page' => $votes->currentPage()]) }})
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.archive_votes') }}</li>
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
                    {{ __('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                {{ __('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                {{ __('main.votes') }}: {{ $vote->count }}<br>
            </div>
        @endforeach
    @else
        {!! showError(__('votes.empty_votes')) !!}
    @endif

    {{ $votes->links('app/_paginator') }}
@stop
