@extends('layout')

@section('title')
    {{ trans('blogs.top_articles') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('blogs.top_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.top_articles') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('main.sort') }}:

    <?php $active = ($order === 'visits') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=visits" class="badge badge-{{ $active }}">{{ trans('main.views') }}</a>

    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=rated" class="badge badge-{{ $active }}">{{ trans('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=comments" class="badge badge-{{ $active }}">{{ trans('main.comments') }}</a>
    <hr>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)

            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>
                {{ trans('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
                {{ trans('main.author') }}: {!! $data->user->getProfile() !!}<br>
                {{ trans('main.views') }}: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('blogs.empty_articles')) !!}
    @endif
@stop
