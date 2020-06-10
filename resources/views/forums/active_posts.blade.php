@extends('layout')

@section('title')
    {{ __('index.forums') }} - {{ __('forums.title_active_posts', ['user' => $user->getName()]) }} ({{ __('main.page_num', ['page' => $posts->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('forums.title_active_posts', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_active_posts', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="section mb-3 shadow">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>

                @if (isAdmin())
                    <a href="#" class="float-right" onclick="return deletePost(this)" data-tid="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                @endif

                <div class="section-message">
                    {!! bbCode($data->text) !!}<br>

                    {{ __('main.posted') }}: {{ $data->user->login }}
                    <small>({{ dateFixed($data->created_at) }})</small>

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">({{ $data->brow }}, {{ $data->ip }})</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('forums.posts_not_created')) !!}
    @endif

    {{ $posts->links() }}
@stop
