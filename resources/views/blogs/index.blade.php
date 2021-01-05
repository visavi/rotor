@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.blogs_list'))

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create">{{ __('blogs.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/blogs"><i class="fas fa-wrench"></i></a>
            @endif
        </div>
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

    {{ __('main.new') }}: <a href="/articles">{{ __('blogs.articles') }}</a>, <a href="/articles/comments">{{ __('main.comments') }}</a>
    <hr>

    @foreach ($categories as $key => $category)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-folder-open"></i>
                <a href="/blogs/{{ $category->id }}">{{ $category->name }}</a>

                @if ($category->new)
                    ({{ $category->count_articles + $category->children->sum('count_articles') }}/<span style="color:#ff0000">+{{ $category->new->count_articles }}</span>)
                @else
                    ({{ $category->count_articles + $category->children->sum('count_articles') }})
                @endif
            </div>

            <div class="section-content">
                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <i class="fa fa-angle-right"></i>
                        <b><a href="/blogs/{{ $child->id }}">{{ $child->name }}</a></b>

                        @if ($child->new)
                            ({{ $child->count_articles }}/<span style="color:#ff0000">+{{ $child->new->count_articles }}</span>)
                        @else
                            ({{ $child->count_articles }})
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a> /
    <a href="/blogs/rss">{{ __('main.rss') }}</a>
@stop
