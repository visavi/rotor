@extends('layout')

@section('title', __('blogs.title_create'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form shadow cut">
        <form action="/blogs/create" method="post">
            @csrf
            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">{{ __('blogs.blog') }}</label>

                <?php $inputCategory = (int) getInput('cid', $cid); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                        @if ($category->children->isNotEmpty())
                            @foreach ($category->children as $categorysub)
                                <option value="{{ $categorysub->id }}"{{ ($inputCategory === $categorysub->id && ! $categorysub->closed) ? ' selected' : '' }}{{ $categorysub->closed ? ' disabled' : '' }}>– {{ $categorysub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ __('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('blogs.article') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('maxblogpost') }}" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">{{ __('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags') }}" required>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            @include('app/_upload', ['files' => $files, 'type' => App\Models\Article::$morphName, 'paste' => true])

            <button class="btn btn-primary">{{ __('blogs.add') }}</button>
        </form>
    </div>

    {{ __('blogs.text_create1') }}<br><br>

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a><br><br>
@stop
