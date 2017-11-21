@extends('layout')

@section('title')
    Публикация нового файла
@stop

@section('content')

    <h1>Публикация нового файла</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/load">Категории</a></li>
        <li class="breadcrumb-item active">Публикация</li>
        <li class="breadcrumb-item"><a href="/load/add?act=waiting">Ожидающие</a></li>
        <li class="breadcrumb-item"><a href="/load/active">Проверенные</a></li>
    </ol>

    <form action="/down/create" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">Категория</label>

            <select class="form-control" id="inputCategory" name="category">
                @foreach ($loads as $data)

                    <option value="{{ $data->id }}"{!! ($cid == $data->id) ? ' selected' : '' !!}{!! !empty($data->closed) ? ' disabled' : '' !!}>{{ $data->name }}</option>

                    @if ($data->children->isNotEmpty())
                        @foreach($data->children as $datasub)
                            <option value="{{ $datasub->id }}"{!! $cid == $datasub->id ? ' selected' : '' !!}{!! !empty($datasub->closed) ? ' disabled' : '' !!}>– {{ $datasub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            {!! textError('category') !!}
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">Название:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            {!! textError('title') !!}
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="markItUp">Описание:</label>
            <textarea class="form-control" id="markItUp" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            {!! textError('text') !!}
        </div>

        <button class="btn btn-primary">Загрузить</button>
    </form>

    <div>
        Файл и скриншот вы сможете загрузить после добавления описания<br>
        Если вы ошиблись в названии или описании файла, вы всегда можете его отредактировать
    </div>

    <i class="fa fa-arrow-circle-left"></i> <a href="/load">Категории</a><br>
@stop
