@extends('layout')

@section('title')
    {{ $category->name }} ({{ __('main.page_num', ['page' => $blogs->currentPage()]) }})
@stop

@section('header')

    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">{{ __('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>({{ __('blogs.all_articles') }}: {{ $category->count_blogs }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->id }}?page={{ $blogs->currentPage() }}">{{ __('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b>
                <div class="float-right js-rating">
                    @if (getUser() && getUser('id') !== $data->user_id)
                        <a class="post-rating-down<?= $data->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
                    @endif
                    <span>{!! formatNum($data->rating) !!}</span>
                    @if (getUser() && getUser('id') !== $data->user_id)
                        <a class="post-rating-up<?= $data->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
                    @endif
                </div>
            </div>
            <div>
                {!! $data->shortText() !!}<br>
                {{ __('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                {{ __('main.views') }}: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links() }}

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a>
@stop
