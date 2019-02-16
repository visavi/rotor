@extends('layout')

@section('title')
    {{ trans('blogs.title_create') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.title_create') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form next">
        <form action="/blogs/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">{{ trans('blogs.blog') }}</label>

                <?php $inputCategory = getInput('cid', $cid); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($cats as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ ($inputCategory === $datasub->id && ! $datasub->closed) ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('cid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('blogs.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('blogs.article') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('maxblogpost') }}" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('text') !!}
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">{{ trans('blogs.tags') }}:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags') }}" required>
                {!! textError('tags') !!}
            </div>

            @include('app._upload', ['files' => $files, 'type' => App\Models\Blog::class, 'paste' => true])

            <button class="btn btn-primary">{{ trans('blogs.add') }}</button>
        </form>
    </div><br>

    {{ trans('blogs.text_create1') }}<br>
    {{ trans('blogs.text_create2') }}<br><br>

    <a href="/rules">{{ trans('main.rules') }}</a> /
    <a href="/stickers">{{ trans('main.stickers') }}</a> /
    <a href="/tags">{{ trans('main.tags') }}</a><br><br>
@stop
