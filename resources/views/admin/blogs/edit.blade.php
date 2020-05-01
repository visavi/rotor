@extends('layout')

@section('title')
    {{ __('blogs.title_edit_blog') }} {{ $category->name }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ __('index.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->id }}">{{ $category->name }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_blog') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/blogs/edit/{{ $category->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">{{ __('blogs.parent_blog') }}</label>

                <?php $inputParent = getInput('parent', $category->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">---</option>

                    @foreach ($categories as $data)
                        @if ($data->id === $category->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('parent') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('blogs.name') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">{{ __('main.position') }}:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $category->sort) }}" required>
                <div class="invalid-feedback">{{ textError('sort') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $category->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close') }}</label>
            </div>


            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
