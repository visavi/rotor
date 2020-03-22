@extends('layout')

@section('title')
    {{ __('main.search_request') }} {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item"><a href="/forums/search">{{ __('main.search') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ __('forums.found_topics') }}: {{ $topics->total() }}</p>

    @foreach ($topics as $topic)
        <div class="section mb-3 shadow">
            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
            <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})

            {!! $topic->pagination() !!}
            {{ __('forums.forum') }}: <a href="/topics/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a><br>
            {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
        </div>
    @endforeach

    {{ $topics->links() }}
@stop
