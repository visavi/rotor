@extends('layout')

@section('title')
    Редактирование раздела
@stop

@section('content')

    <h1>Редактирование раздела</h1>

    <div action="/admin/forum/edit/{{ $forum->id }}" class="form mb-3">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">Родительский раздел</label>

                <?php $inputParent = getInput('parent', $forum->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">Основной форум</option>

                    @foreach ($forums as $data)

                        @if ($data->id == $forum->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent == $data->id) ? ' selected' : '' }}{{ !empty($data->closed) ? ' disabled' : '' }}>{{ $data->title }}</option>
                    @endforeach

                </select>
                {!! textError('parent') !!}
            </div>


            <div class="form-group{{ hasError('title') }}">
                <label for="title">Название:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $forum->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('description') }}">
                <label for="description">Описание:</label>
                <input class="form-control" name="description" id="description" maxlength="100" value="{{ getInput('description', $forum->description) }}">
                {!! textError('description') !!}
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">Положение:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $forum->sort) }}" required>
                {!! textError('sort') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $forum->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть раздел</label>
            </div>


            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
