@extends('layout')

@section('title', __('blogs.title_move_article') . ' ' . $article->title)

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
            <li class="breadcrumb-item active">{{ __('blogs.title_move_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form shadow cut">
        <form action="/admin/articles/move/{{ $article->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">{{ __('blogs.blog') }}</label>

                <?php $inputCategory = (int) getInput('cid', $article->category_id); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                        @if ($category->children->isNotEmpty())
                            @foreach ($category->children as $categorysub)
                                <option value="{{ $categorysub->id }}"{{ ($inputCategory === $categorysub->id && !$categorysub->closed) ? ' selected' : '' }}{{ $categorysub->closed ? ' disabled' : '' }}>– {{ $categorysub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.move') }}</button>
        </form>
    </div>
@stop
