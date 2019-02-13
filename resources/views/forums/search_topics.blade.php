@extends('layout')

@section('title')
    {{ trans('common.search_request') }} {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item"><a href="/forums/search">{{ trans('common.search') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('common.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ trans('forums.found_topics') }}: {{ $page->total }}</p>

    @foreach ($topics as $topic)
        <div class="b">
            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
            <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
        </div>
        <div>
            {!! $topic->pagination() !!}
            {{ trans('forums.forum') }}: <a href="/topics/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a><br>
            {{ trans('forums.message') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
