@extends('layout')

@section('title')
    {{ trans('forums.forum') }} - {{ trans('forums.title_new_posts') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('forums.title_new_posts') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_new_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($posts as $data)
        <div class="b">
            <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
            ({{ $data->topic->count_posts }})
        </div>
        <div>
            {!! bbCode($data->text) !!}<br>

            {{ trans('forums.posted_by') }}: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small><br>

            @if (isAdmin())
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            @endif

        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
