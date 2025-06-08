@extends('layout')

@section('title', __('blogs.title_edit_blog') . ' ' . $category->name)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ __('blogs.title_edit_blog') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="{{ route('admin.blogs.edit', ['id' => $category->id]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('parent') }}">
                <label for="parent" class="form-label">{{ __('blogs.parent_blog') }}</label>

                <?php $inputParent = (int) getInput('parent', $category->parent_id); ?>

                <select class="form-select" id="parent" name="parent">
                    <option value="0">---</option>

                    @foreach ($categories as $data)
                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed || $data->id === $category->id ? ' disabled' : '' }}>
                            {{ str_repeat('–', $data->depth) }} {{ $data->name }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('parent') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('blogs.name') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="mb-3{{ hasError('sort') }}">
                <label for="sort" class="form-label">{{ __('main.position') }}:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $category->sort) }}" required>
                <div class="invalid-feedback">{{ textError('sort') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="form-check-input" value="1" name="closed" id="closed"{{ getInput('closed', $category->closed) ? ' checked' : '' }}>
                <label class="form-check-label" for="closed">{{ __('main.close') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
