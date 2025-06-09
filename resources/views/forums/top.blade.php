@extends('layout')

@section('title', __('forums.title_top_topics'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_top_topics') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        @foreach ($topics as $data)
            <div class="section mb-3 shadow">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="{{ route('topics.topic', ['id' => $data->id]) }}">{{ $data->title }}</a></b> <span class="badge bg-adaptive">{{ $data->count_posts }}</span>

                {{ $data->pagination() }}
                {{ __('main.author') }}: {{ $data->user->getName() }}<br>
                {{ __('forums.post') }}: {{ $data->lastPost->user->getName() }} ({{ dateFixed($data->lastPost->created_at) }})
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_topics')) }}
    @endif

    {{ $topics->links() }}
@stop
