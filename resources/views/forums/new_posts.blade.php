@extends('layout')

@section('title')
    {{ __('index.forums') }} - {{ __('forums.title_new_posts') }} ({{ __('main.page_num', ['page' => $posts->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('forums.title_new_posts') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_new_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
                ({{ $data->topic->count_posts }})
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                {{ __('main.posted') }}: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

            </div>
        @endforeach
    @else
        {!! showError(__('forums.posts_not_created')) !!}
    @endif

    {{ $posts->links('app/_paginator') }}
@stop
