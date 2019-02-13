@extends('layout')

@section('title')
    {{ trans('common.search_request') }} {{ $find }}
@stop

@section('header')
    <h1>{{ trans('common.search_request') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="/blogs/search">{{ trans('common.search') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('common.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ trans('blogs.found_in_titles') }}: {{ $page->total }}</p>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
        </div>

        <div>
            {{ trans('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            {{ trans('common.views') }}: {{ $data->visits }}<br>
            {{ trans('common.author') }}: {!! $data->user->getProfile() !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
