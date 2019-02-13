@extends('layout')

@section('title')
    Чистка пользователей
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item active">Чистка пользователей</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isEmpty())
        Удалить пользователей которые не посещали сайт:<br>

        <div class="form">
            <form action="/admin/delusers" method="post">

                <div class="form-group">
                    <label for="period">Период:</label>
                    <select class="form-control" id="period" name="period">
                        <option value="1825">5 лет</option>
                        <option value="1460">4 года</option>
                        <option value="1095">3 года</option>
                        <option value="730">2 года</option>
                        <option value="550">1.5 года</option>
                        <option value="365">1 год</option>
                        <option value="180">0.5 года</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="point">Минимум актива:</label>
                    <input type="text" class="form-control" id="point" name="point"  value="0" required>
                </div>

                <button class="btn btn-primary">Анализ</button>
            </form>
        </div><br>

        Всего пользователей: <b>{{ $total }}</b><br><br>
    @else

        Будут удалены пользователи не посещавшие сайт более <b>{{ $period }}</b> дней <br>
        И имеющие в своем активе не более {{ plural($point, setting('scorename')) }}<br><br>

        <b>Список:</b>

        @foreach ($users as $user)

            <?php $comma = $loop->first ? '' : ',' ?>

            {{ $comma }} {!! $user->getProfile() !!}
        @endforeach

        <br><br>Будет удалено пользователей: <b>{{ $users->count() }}</b><br>

        <form action="/admin/delusers/clear" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="point" value="{{ $point }}">

            <button class="btn btn-primary">Удалить пользователей</button>
        </form><br>
    @endif
@stop
