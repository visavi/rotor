@extends('layout')

@section('title')
    {{ __('blogs.title_edit_article') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @if ($article->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $article->category->parent->id }}">{{ $article->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/blogs/{{ $article->category->id }}">{{ $article->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form cut">
        <form action="/articles/edit/{{ $article->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">{{ __('blogs.blog') }}</label>

                <?php $inputCategory = getInput('cid', $article->category_id); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                        @if ($category->children->isNotEmpty())
                            @foreach($category->children as $categoriesub)
                                <option value="{{ $categoriesub->id }}"{{ ($inputCategory === $categoriesub->id && ! $category->closed) ? ' selected' : '' }}{{ $categoriesub->closed ? ' disabled' : '' }}>– {{ $categoriesub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ __('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $article->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('blogs.article') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('maxblogpost') }}" id="text" rows="5" name="text" required>{{ getInput('text', $article->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">{{ __('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags', $article->tags) }}" required>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            @include('app/_upload', ['id' => $article->id, 'files' => $article->files, 'type' => $article->getMorphClass(), 'paste' => true])

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div><br>

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a><br><br>
@stop
