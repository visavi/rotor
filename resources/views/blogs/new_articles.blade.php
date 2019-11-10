@extends('layout')

@section('title')
    {{ __('index.blogs') }} - {{ __('blogs.new_articles') }} ({{ __('main.page_num', ['page' => $blogs->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('blogs.new_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.new_articles') }}</li>
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
                {{ __('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->category->name }}</a><br>
                {{ __('main.views') }}: {{ $data->visits }}<br>
                {{ __('main.author') }}: {!! $data->user->getProfile() !!}  ({{  dateFixed($data->created_at) }})
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links('app/_paginator') }}
@stop
