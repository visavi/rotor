@extends('layout')

@section('title', __('blogs.title_move_article') . ' ' . $article->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_move_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow cut">
        <form action="/admin/articles/{{ $article->id }}/move" method="post">
            @csrf
            <div class="mb-3{{ hasError('cid') }}">
                <label for="inputCategory" class="form-label">{{ __('blogs.blog') }}</label>

                <?php $inputCategory = (int) getInput('cid', $article->category_id); ?>
                <select class="form-select" id="inputCategory" name="cid">

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed && $category->id !== $article->category_id) ? ' selected' : '' }}{{ $category->closed || $category->id === $article->category_id ? ' disabled' : '' }}>
                            {{ str_repeat('–', $category->depth) }} {{ $category->name }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.move') }}</button>
        </form>
    </div>
@stop
