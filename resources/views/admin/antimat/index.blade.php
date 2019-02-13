@extends('layout')

@section('title')
    Антимат
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Антимат</li>
        </ol>
    </nav>
@stop

@section('content')
    Все слова из списка будут заменяться на ***<br>
    Чтобы удалить слово нажмите на него, добавить слово можно в форме ниже<br><br>

    @if ($words->isNotEmpty())

        <div class="card">
            <h2 class="card-header">
                Список слов
            </h2>

            <div class="card-body">
                @foreach ($words as $data)
                    <a href="/admin/antimat/delete?id={{ $data->id }}&amp;token={{ $_SESSION['token'] }}">{{ $data->string }}</a>{{ $loop->last ? '' : ', ' }}
                @endforeach
            </div>

            <div class="card-footer">
                Всего слов в базе: <b>{{ $words->count() }}</b>

                @if (isAdmin('boss'))
                    <span class="float-right">
                        <i class="fa fa-trash-alt"></i> <a href="/admin/antimat/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить все слова?')">Очистить</a>
                    </span>
                @endif
            </div>
        </div>
        <br>

    @else
        {!! showError('Слов еще нет!') !!}
    @endif

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                </div>

                <input type="text" class="form-control" name="word" placeholder="Введите слово" required>

                <span class="input-group-btn">
                    <button class="btn btn-primary">Добавить</button>
                </span>
            </div>
        </form>
    </div>
@stop
