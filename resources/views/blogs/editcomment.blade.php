@extends('layout')

@section('title', __('blogs.title_edit_comment'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('app/_comment_edit_form', [
        'action' => route('articles.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]),
    ])
@stop
