@extends('layout')

@section('title', sprintf('%s - %s (%s)', __('index.forums'), __('forums.title_active_posts', ['user' => $user->getName()]), __('main.page_num', ['page' => $posts->currentPage()])))

@section('header')
    <h1>{{ __('forums.title_active_posts', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_active_posts', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('forums.active-posts', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc', 'user' => $user->login]) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($posts as $data)
            <div class="section mb-3 shadow">
                <i class="fa fa-file-alt"></i> <b><a href="{{ route('topics.topic', ['id' => $data->topic_id, 'pid' => $data->id]) }}">{{ $data->topic->title }}</a></b>

                @if (isAdmin())
                    <a href="{{ route('forums.active-delete', ['id' => $data->id]) }}" class="float-end" onclick="return deletePost(this)" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                @endif

                <div class="section-message">
                    {{ bbCode($data->text) }}<br>

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">({{ $data->brow }}, {{ $data->ip }})</div>
                    @endif
                </div>

                {{ __('main.rating') }}: <span class="badge bg-adaptive">{{ formatNum($data->rating) }}</span><br>
                {{ __('main.posted') }}: {{ $data->user->getName() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.posts_not_created')) }}
    @endif

    {{ $posts->links() }}
@stop
