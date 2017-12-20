@extends('layout')

@section('title')
    Статусы пользователей
@stop

@section('content')

    <h1>Статусы пользователей</h1>

    @if ($statuses->isNotEmpty())

        <div class="card">
            <h2 class="card-header">
                Список
            </h2>

            <ul class="list-group list-group-flush">
                @foreach ($statuses as $status)
                    <li class="list-group-item">
                        <span{!! $status->color ? ' style="color:' . $status->color . '"' : '' !!}>
                            <i class="fa fa-user-circle"></i> <b>{{ $status->name }}</b>
                        </span>

                        <small>({{ $status->topoint }} - {{ $status->point }})</small>

                        <div class="float-right">
                            <a data-toggle="tooltip" title="Редактировать" href="/admin/status/edit?id={{ $status->id }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            <a data-toggle="tooltip" title="Удалить" href="/admin/status/delete?id={{ $status->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить выбранный статус?')"><i class="fa fa-trash-alt text-muted"></i></a>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="card-footer">
                Всего статусов: <b>{{ $statuses->count() }}</b>
            </div>
        </div><br>

    @else
        {!! showError('Статусы еще не назначены!') !!}
    @endif

    <i class="fa fa-plus"></i> <a href="/admin/status/create">Создать</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
