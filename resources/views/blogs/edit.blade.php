@extends('layout')

@section('title')
    {{ trans('blogs.title_edit_article') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/articles/{{ $blog->id }}">{{ $blog->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/articles/edit/{{ $blog->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">{{ trans('blogs.blog') }}</label>

                <?php $inputCategory = getInput('cid', $blog->category_id); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($categories as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ ($inputCategory === $datasub->id && ! $data->closed) ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('cid') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $blog->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('blogs.article') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('maxblogpost') }}" id="text" rows="5" name="text" required>{{ getInput('text', $blog->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">{{ trans('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags', $blog->tags) }}" required>
                <div class="invalid-feedback">{{ textError('tags') }}</div>
            </div>

            @include('app/_upload', ['id' => $blog->id, 'files' => $blog->files, 'type' => App\Models\Blog::class, 'paste' => true])

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div><br>

    <a href="/rules">{{ trans('main.rules') }}</a> /
    <a href="/stickers">{{ trans('main.stickers') }}</a> /
    <a href="/tags">{{ trans('main.tags') }}</a><br><br>
@stop
