@extends('layout')

@section('title', __('index.forums') . ' - ' . __('forums.title_active_topics', ['user' => $user->getName()]) . ' (' . __('main.page_num', ['page' => $topics->currentPage()])  . ')')

@section('header')
    <h1>{{ __('forums.title_active_topics', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_active_topics', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('forums.active-topics', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc', 'user' => $user->login]) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($topics as $data)
            <div class="section mb-3 shadow">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="{{ route('topics.topic', ['id' => $data->id]) }}">{{ $data->title }}</a></b> <span class="badge bg-adaptive">{{ $data->count_posts }}</span>

                {{ $data->pagination() }}
                {{ __('forums.forum') }}: <a href="{{ route('forums.forum', ['id' => $data->forum->id]) }}">{{ $data->forum->title }}</a><br>
                {{ __('main.views') }}: <span class="badge bg-adaptive">{{ $data->visits }}</span><br>
                {{ __('main.author') }}: {{ $data->user->getName() }} / {{ __('forums.latest') }}.: {{ $data->lastPost->user->getName() }} <small class="section-date text-muted fst-italic">{{ dateFixed($data->lastPost->created_at) }}</small>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.topics_not_created')) }}
    @endif

    {{ $topics->links() }}
@stop
