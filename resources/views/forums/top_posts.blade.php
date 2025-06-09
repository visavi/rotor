@extends('layout')

@section('title', __('forums.title_top_posts'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_top_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.period') }}:
    <?php $active = ($period === 1) ? 'success' : 'adaptive'; ?>
    <a href="{{ route('forums.top-posts', ['period' => 1]) }}" class="badge bg-{{ $active }}">{{ __('main.last_day') }}</a>

    <?php $active = ($period === 7) ? 'success' : 'adaptive'; ?>
    <a href="{{ route('forums.top-posts', ['period' => 7]) }}" class="badge bg-{{ $active }}">{{ __('main.last_week') }}</a>

    <?php $active = ($period === 30) ? 'success' : 'adaptive'; ?>
    <a href="{{ route('forums.top-posts', ['period' => 30]) }}" class="badge bg-{{ $active }}">{{ __('main.last_month') }}</a>

    <?php $active = ($period === 365) ? 'success' : 'adaptive'; ?>
    <a href="{{ route('forums.top-posts', ['period' => 365]) }}" class="badge bg-{{ $active }}">{{ __('main.last_year') }}</a>

    <?php $active = (empty($period)) ? 'success' : 'adaptive'; ?>
    <a href="{{ route('forums.top-posts') }}" class="badge bg-{{ $active }}">{{ __('main.all_time') }}</a>
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="section mb-3 shadow">
                <i class="fa fa-file-alt"></i> <b><a href="{{ route('topics.topic', ['id' => $data->topic_id, 'pid' => $data->id]) }}">{{ $data->topic->title }}</a></b>
                ({{ __('main.rating') }}: {{ $data->rating }})

                <div class="section-message">
                    {{ bbCode($data->text) }}<br>

                    {{ __('main.posted') }}: {{ $data->user->getName() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">({{ $data->brow }}, {{ $data->ip }})</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_posts')) }}
    @endif

    {{ $posts->links() }}
@stop
