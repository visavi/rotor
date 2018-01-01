@extends('layout')

@section('title')
    Шаблоны писем
@stop

@section('content')

    <h1>Шаблоны писем</h1>

    @if ($notices->isNotEmpty())

    @foreach ($notices as $notice)

        <div class="b">

            <i class="fa fa-envelope"></i> <b><a href="/admin/notice/edit/{{ $notice->id }}">{{ $notice->name }}</a></b>

            <div class="float-right">
                @if ($notice->protect)
                    <i class="fa fa-lock"></i>
                @else
                    <a href="/admin/notice/delete/{{ $notice->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данный шаблон?')"><i class="fa fa-trash-alt"></i></a>
                @endif
            </div>
        </div>

        <div>
            <span class="badge badge-info">Тип шаблона: {{ $notice->type }}</span><br>
            Изменено: {!! profile($notice->user) !!}
            ({{ dateFixed($notice->updated_at) }})
        </div>
    @endforeach

    <br>Всего шаблонов: {{ $notices->count() }}<br><br>

    @else
        {!! showError('Шаблонов еще нет!') !!}
    @endif

    <i class="fa fa-check"></i> <a href="/admin/notice/create">Добавить</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
