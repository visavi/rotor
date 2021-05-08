@extends('layout')

@section('title', __('blogs.title_edit_article') . ' ' . $article->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ __('index.blogs') }}</a></li>

            @if ($article->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $article->category->parent->id }}">{{ $article->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/blogs/{{ $article->category->id }}">{{ $article->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow cut">
        <form action="/admin/articles/edit/{{ $article->id }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $article->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('blogs.article') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $article->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="mb-3{{ hasError('tags') }}">
                <label for="inputTags" class="form-label">{{ __('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags', $article->tags) }}" required>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            @include('app/_upload_image', ['id' => $article->id, 'files' => $article->files, 'type' => $article->getMorphClass(), 'paste' => true])

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
