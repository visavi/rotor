@extends('layout')

@section('title', __('blogs.title_edit_article'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/blogs/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow cut">
        <form action="/articles/{{ $article->id }}/edit" method="post">
            @csrf
            <div class="mb-3{{ hasError('cid') }}">
                <label for="inputCategory" class="form-label">{{ __('blogs.blog') }}</label>

                <?php $inputCategory = (int) getInput('cid', $article->category_id); ?>
                <select class="form-select" id="inputCategory" name="cid">

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>
                            {{ str_repeat('–', $category->depth) }} {{ $category->name }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $article->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('blogs.article') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('maxblogpost') }}" id="text" rows="5" name="text" required>{{ getInput('text', $article->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <?php $inputTags = getInput('tags', $article->tags->pluck('name')); ?>
            <div class="mb-3{{ hasError('tags') }}">
                <label for="tags" class="form-label">{{ __('blogs.tags') }}:</label>
                <select class="form-select input-tag" id="tags" name="tags[]" multiple required>
                    <option disabled value="">{{ __('blogs.tags') }}...</option>
                    @foreach ($inputTags as $tag)
                        <option value="{{ $tag }}" selected>{{ $tag }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            @include('app/_upload_image', ['id' => $article->id, 'files' => $article->files, 'type' => $article->getMorphClass(), 'paste' => true])

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a><br><br>
@stop
