@extends('layout')

@section('title')
    {{ __('blogs.title_tags') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_tags') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ __('blogs.found_in_tags') }}: {{ $blogs->total() }}</p>

    @foreach($blogs as $data)
        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> (<?=formatNum($data->rating)?>)
        </div>

        <div>
            {{ __('blogs.blog') }}: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            {{ __('main.views') }}: {{ $data->visits }}<br>
            {{ __('blogs.tags') }}: {{ $data->tags }}<br>
            {{ __('main.author') }}: {!! $data->user->getProfile() !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {{ $blogs->links() }}
@stop
