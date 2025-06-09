@extends('layout')

@section('title', __('index.forums') . ' - ' . __('forums.title_new_posts') . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('header')
    <h1>{{ __('forums.title_new_posts') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_new_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="section mb-3 shadow">
                <i class="fa fa-file-alt"></i> <b><a href="{{ route('topics.topic', ['id' => $data->topic_id, 'pid' => $data->id]) }}">{{ $data->topic->title }}</a></b>
                <span class="badge bg-adaptive">{{ $data->topic->count_posts }}</span>

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
        {{ showError(__('forums.posts_not_created')) }}
    @endif

    {{ $posts->links() }}
@stop
