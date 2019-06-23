@extends('layout')

@section('title')
    {{ trans('blogs.title_edit_article') }} {{ $blog->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/blogs/{{ $blog->category->id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/articles/{{ $blog->id }}">{{ $blog->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/articles/edit/{{ $blog->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $blog->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('blogs.article') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $blog->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">{{ trans('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags', $blog->tags) }}" required>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            <div class="js-images">
                @if ($blog->files->isNotEmpty())
                    @foreach ($blog->files as $file)
                        <span class="js-image">
                            {!! resizeImage($file->hash, ['width' => 100, 'onclick' => 'return pasteImage(this);']) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ App\Models\Blog::class }}"  data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>
@stop
