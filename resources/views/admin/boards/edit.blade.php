@extends('layout')

@section('title')
    Редактирование раздела {{ $board->name }}
@stop

@section('content')

    <h1>Редактирование раздела {{ $board->name }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards">Объявления</a></li>
            <li class="breadcrumb-item"><a href="/admin/boards/categories">Категории</a></li>

            @if ($board->parent->id)
                <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->id }}">{{ $board->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование раздела</li>
        </ol>
    </nav>


    <div class="form mb-3">
        <form action="/admin/boards/edit/{{ $board->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">Родительский раздел</label>

                <?php $inputParent = getInput('parent', $board->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">Основной форум</option>

                    @foreach ($boards as $data)

                        @if ($data->id === $board->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>
                    @endforeach

                </select>
                {!! textError('parent') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $board->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">Положение:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $board->sort) }}" required>
                {!! textError('sort') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $board->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть раздел</label>
            </div>


            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
