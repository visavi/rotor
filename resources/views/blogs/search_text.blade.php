@extends('layout')

@section('title')
    {{ trans('main.search_request') }} {{ $find }}
@stop

@section('header')
    <h1>{{ trans('main.search_request') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="/blogs/search">{{ trans('main.search') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ trans('blogs.found_in_text') }}: {{ $page->total }}</p>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
        </div>

        <div>
            {!! stripString(bbCode($data->text), 200) !!}<br>
            {{ trans('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            {{ trans('main.author') }}: {!! $data->user->getProfile() !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
