@extends('layout')

@section('title', sprintf('%s (%s)', __('votes.archive_votes'), __('main.page_num', ['page' => $votes->currentPage()])))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('votes.index') }}">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.archive_votes') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-briefcase"></i>
                    <a href="{{ route('votes.view-history', ['id' => $vote->id]) }}">{{ $vote->title }}</a>
                </div>
                <div class="section-body">
                    @if ($vote->topic->id)
                        {{ __('forums.topic') }}: <a href="{{ route('topics.topic', ['id' => $vote->topic->id]) }}">{{ $vote->topic->title }}</a><br>
                    @endif

                    {{ __('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                    {{ __('main.votes') }}: {{ $vote->count }}
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('votes.empty_votes')) }}
    @endif

    {{ $votes->links() }}
@stop
