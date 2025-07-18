@extends('layout')

@section('title', __('blogs.title_edit_article'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow cut">
        @include('blogs/_form')
    </div>

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a><br><br>
@stop
