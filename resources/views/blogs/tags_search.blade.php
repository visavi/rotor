@extends('layout')

@section('title')
    {{ trans('blogs.title_tags') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="/blogs/tags">{{ trans('blogs.tag_cloud') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_tags') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ trans('blogs.found_in_tags') }}: {{ $page->total }}</p>

    @foreach($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> (<?=formatNum($data->rating)?>)
        </div>

        <div>
            {{ trans('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            {{ trans('main.views') }}: {{ $data->visits }}<br>
            {{ trans('blogs.tags') }}: {{ $data->tags }}<br>
            {{ trans('main.author') }}: {!! $data->user->getProfile() !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
