@extends('layout')

@section('title')
    {{ __('index.blogs') }} - {{ __('blogs.blogs_list') }}
@stop

@section('header')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create">{{ __('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>{{ __('index.blogs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.blogs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ __('main.my') }}: <a href="/blogs/active/articles">{{ __('blogs.articles') }}</a>, <a href="/blogs/active/comments">{{ __('main.comments') }}</a> /
    @endif

    {{ __('main.new') }}: <a href="/articles">{{ __('blogs.articles') }}</a>, <a href="/articles/comments">{{ __('main.comments') }}</a><hr>

    @foreach ($categories as $key => $data)
        <div class="b">
            <i class="fa fa-folder-open"></i> <b><a href="/blogs/{{ $data->id }}">{{ $data->name }}</a></b>

            @if ($data->new)
                ({{ $data->count_blogs + $data->children->sum('count_blogs') }}/<span style="color:#ff0000">+{{ $data->new->count_blogs }}</span>)
            @else
                ({{ $data->count_blogs + $data->children->sum('count_blogs') }})
            @endif
        </div>

        <div>
            @if ($data->children->isNotEmpty())
                @foreach ($data->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/blogs/{{ $child->id }}">{{ $child->name }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_blogs }}/<span style="color:#ff0000">+{{ $child->new->count_blogs }}</span>)
                    @else
                        ({{ $child->count_blogs }})
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

    <br>
    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a> /
    <a href="/blogs/rss">{{ __('main.rss') }}</a>
@stop
