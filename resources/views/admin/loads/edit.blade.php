@extends('layout')

@section('title')
    Редактирование раздела {{ $load->name }}
@stop

@section('content')

    <h1>Редактирование раздела {{ $load->name }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">Загрузки</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads/{{ $load->id }}">{{ $load->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
        </ol>
    </nav>

    <div class="form mb-3">
        <form action="/admin/loads/edit/{{ $load->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('parent') }}">
                <label for="parent">Родительский раздел</label>

                <?php $inputParent = getInput('parent', $load->parent_id); ?>

                <select class="form-control" id="parent" name="parent">
                    <option value="0">Основной форум</option>

                    @foreach ($loads as $data)

                        @if ($data->id == $load->id)
                            @continue
                        @endif

                        <option value="{{ $data->id }}"{{ ($inputParent == $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>
                    @endforeach

                </select>
                {!! textError('parent') !!}
            </div>


            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $load->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('sort') }}">
                <label for="sort">Положение:</label>
                <input type="number" class="form-control" name="sort" id="sort" maxlength="2" value="{{ getInput('sort', $load->sort) }}" required>
                {!! textError('sort') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $load->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть раздел</label>
            </div>


            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
