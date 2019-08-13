@extends('layout')

@section('title')
    {{ trans('index.blogs') }} - {{ trans('blogs.new_articles') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('blogs.new_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.new_articles') }}</li>
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

            <div>
                {{ trans('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->category->name }}</a><br>
                {{ trans('main.views') }}: {{ $data->visits }}<br>
                {{ trans('main.author') }}: {!! $data->user->getProfile() !!}  ({{  dateFixed($data->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('blogs.empty_articles')) !!}
    @endif
@stop
