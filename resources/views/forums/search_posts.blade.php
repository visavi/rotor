@extends('layout')

@section('title')
    {{ trans('main.search_request') }} {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item"><a href="/forums/search">{{ trans('main.search') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ trans('forums.found_posts') }}: {{ $page->total }}</p>

    @foreach ($posts as $post)
        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $post->topic_id }}/{{ $post->id }}">{{ $post->topic->title }}</a></b>
        </div>

        <div>{!! bbCode($post->text) !!}<br>
            {{ trans('forums.forum') }}: <a href="/topics/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a><br>
            {{ trans('forums.posted_by') }}: {!! $post->user->getProfile() !!} <small>({{ dateFixed($post->created_at) }})</small><br>
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
