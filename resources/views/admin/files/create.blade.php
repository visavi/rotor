@extends('layout')

@section('title')
    Создание нового объекта
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/files">Редактирование страниц</a></li>
            <li class="breadcrumb-item"><a href="/admin/files?path={{ $path }}">{{ $path }}</a></li>
            <li class="breadcrumb-item active">Создание нового объекта</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 bg-light p-1">
                <form action="/admin/files/create?path={{ $path }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('dirname') }}">
                        <label for="dirname">Название директории:</label>
                        <input type="text" class="form-control" id="dirname" name="dirname" maxlength="30" value="{{ getInput('dirname') }}" required>
                        {!! textError('dirname') !!}
                    </div>

                    <button class="btn btn-primary">Создать директорию</button>
                </form>
            </div>

            <div class="col-md-6 bg-light p-1">
                <form action="/admin/files/create?path={{ $path }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('filename') }}">
                        <label for="filename">Название файла (без расширения):</label>
                        <input type="text" class="form-control" id="filename" name="filename" maxlength="30" value="{{ getInput('filename') }}" required>
                        {!! textError('filename') !!}
                    </div>

                    <button class="btn btn-primary">Создать файл</button>
                </form>
            </div>
        </div>

        <p class="text-muted font-italic">Разрешены латинские символы и цифры, а также знаки дефис и нижнее подчеркивание</p>
    </div>
@stop
