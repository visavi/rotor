@extends('layout')

@section('title')
    {{ trans('blogs.blogs') }} - {{ trans('blogs.title_active_articles', ['user' => $user->login]) }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('blogs.title_active_articles', ['user' => $user->login]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_active_articles', ['user' => $user->login]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>{{ trans('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('blogs.total_articles') }}: <b>{{ $page->total }}</b><br>
    @else
        {!! showError(trans('blogs.empty_articles')) !!}
    @endif
@stop
