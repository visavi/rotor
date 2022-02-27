@extends('layout')

@section('title', __('boards.edit_category') . ' ' . $board->name)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards/categories">{{ __('boards.categories') }}</a></li>

            @foreach ($board->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/admin/boards/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ __('boards.edit_category') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/boards/edit/{{ $board->id }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('parent') }}">
                <label for="parent" class="form-label">{{ __('boards.parent_category') }}</label>

                <?php $inputParent = (int) getInput('parent', $board->parent_id); ?>

                <select class="form-select" id="parent" name="parent">
                    <option value="0">---</option>

                    @foreach ($boards as $data)
                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed || $data->id === $board->id ? ' disabled' : '' }}>
                            {{ str_repeat('–', $data->depth) }} {{ $data->name }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('parent') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('boards.name') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $board->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="mb-3{{ hasError('sort') }}">
                <label for="sort" class="form-label">{{ __('main.position') }}:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $board->sort) }}" required>
                <div class="invalid-feedback">{{ textError('sort') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="form-check-input" value="1" name="closed" id="closed"{{ getInput('closed', $board->closed) ? ' checked' : '' }}>
                <label class="form-check-label" for="closed">{{ __('main.close') }}</label>
            </div>


            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
