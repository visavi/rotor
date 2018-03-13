@extends('layout')

@section('title')
    Редактирование раздела {{ $category->name }}
@stop

@section('content')

    <h1>Редактирование раздела {{ $category->name }}</h1>

    <div action="/admin/blog/edit/{{ $category->id }}" class="form mb-3">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">Родительский раздел</label>

                <?php $inputParent = getInput('parent', $category->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">Основной форум</option>

                    @foreach ($categories as $data)

                        @if ($data->id == $category->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent == $data->id) ? ' selected' : '' }}{{ !empty($data->closed) ? ' disabled' : '' }}>{{ $data->name }}</option>
                    @endforeach

                </select>
                {!! textError('parent') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">Положение:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $category->sort) }}" required>
                {!! textError('sort') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $category->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть раздел</label>
            </div>


            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
