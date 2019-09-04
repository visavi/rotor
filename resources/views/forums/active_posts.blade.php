@extends('layout')

@section('title')
    {{ __('index.forums') }} - {{ __('forums.title_active_posts', ['user' => $user->login]) }} ({{ __('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ __('forums.title_active_posts', ['user' => $user->login]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_active_posts', ['user' => $user->login]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($posts as $data)
        <div class="post">
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>

                @if (isAdmin())
                    <a href="#" class="float-right" onclick="return deletePost(this)" data-tid="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                @endif
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                {{ __('main.posted') }}: {{ $data->user->login }}
                <small>({{ dateFixed($data->created_at) }})</small>
                <br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
