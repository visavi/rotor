@extends('layout')

@section('title')
    {{ __('boards.edit_category') }} {{ $board->name }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards/categories">{{ __('boards.categories') }}</a></li>

            @if ($board->parent->id)
                <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->id }}">{{ $board->name }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.edit_category') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/boards/edit/{{ $board->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">{{ __('boards.parent_category') }}</label>

                <?php $inputParent = getInput('parent', $board->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">---</option>

                    @foreach ($boards as $data)

                        @if ($data->id === $board->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('parent') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('boards.name') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $board->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">{{ __('main.position') }}:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $board->sort) }}" required>
                <div class="invalid-feedback">{{ textError('sort') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $board->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close') }}</label>
            </div>


            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
