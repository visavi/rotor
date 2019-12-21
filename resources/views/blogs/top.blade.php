@extends('layout')

@section('title')
    {{ __('blogs.top_articles') }} ({{ __('main.page_num', ['page' => $blogs->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('blogs.top_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.top_articles') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.sort') }}:

    <?php $active = ($order === 'visits') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=visits" class="badge badge-{{ $active }}">{{ __('main.views') }}</a>

    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=rated" class="badge badge-{{ $active }}">{{ __('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=comments" class="badge badge-{{ $active }}">{{ __('main.comments') }}</a>
    <hr>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)

            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>
                {{ __('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
                {{ __('main.author') }}: {!! $data->user->getProfile() !!}<br>
                {{ __('main.views') }}: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links() }}
@stop
