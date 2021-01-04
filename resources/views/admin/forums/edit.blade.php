@extends('layout')

@section('title', __('forums.title_edit_forum') . ' ' . $forum->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_edit_forum') }} {{ $forum->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/forums/edit/{{ $forum->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">{{ __('forums.parent_forum') }}</label>

                <?php $inputParent = (int) getInput('parent', $forum->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">---</option>
                    @foreach ($forums as $data)
                        @if ($data->id === $forum->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->title }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('parent') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ __('forums.forum') }}:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $forum->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('description') }}">
                <label for="description">{{ __('main.description') }}:</label>
                <input class="form-control" name="description" id="description" maxlength="100" value="{{ getInput('description', $forum->description) }}">
                <div class="invalid-feedback">{{ textError('description') }}</div>
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">{{ __('main.position') }}:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $forum->sort) }}" required>
                <div class="invalid-feedback">{{ textError('sort') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $forum->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
