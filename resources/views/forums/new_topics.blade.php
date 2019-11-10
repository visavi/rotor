@extends('layout')

@section('title')
    {{ __('index.forums') }} - {{ __('forums.title_new_topics') }} ({{ __('main.page_num', ['page' => $topics->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('forums.title_new_topics') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_new_topics') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        @foreach ($topics as $data)
            <div class="b">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="/topics/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_posts }})
            </div>

            <div>
                {!! $data->pagination() !!}
                {{ __('forums.forum') }}: <a href="/forums/{{  $data->forum->id }}">{{  $data->forum->title }}</a><br>
                {{ __('main.author') }}: {{ $data->user->getName() }} / Посл.: {{ $data->lastPost->user->getName() }} ({{ dateFixed($data->lastPost->created_at) }})
            </div>
        @endforeach
    @else
        {!! showError(__('forums.topics_not_created')) !!}
    @endif

    {{ $topics->links('app/_paginator') }}
@stop
