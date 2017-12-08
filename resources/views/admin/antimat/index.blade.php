@extends('layout')

@section('title')
    Управление антиматом
@stop

@section('content')

    <h1>Управление антиматом</h1>

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
                <span class="input-group-addon">
                    <i class="fa fa-pencil-alt"></i>
                </span>

                <input type="text" class="form-control" name="word" placeholder="Введите слово" required>

                <span class="input-group-btn">
                    <button class="btn btn-primary">Добавить</button>
                </span>
            </div>
        </form>
    </div>
    <br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
