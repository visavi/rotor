@extends('layout')

@section('title')
    Управление жалобами - @parent
@stop

@section('content')

    <h1>Управление жалобами</h1>

    <?php $active = ($type == 'post') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=post" class="badge badge-{{ $active }}">Форум {{ $total['post'] }}</a>
    <?php $active = ($type == 'guest') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=guest" class="badge badge-{{ $active }}">Гостевая {{ $total['guest'] }}</a>
    <?php $active = ($type == 'photo') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=photo" class="badge badge-{{ $active }}">Галерея {{ $total['photo'] }}</a>
    <?php $active = ($type == 'blog') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=blog" class="badge badge-{{ $active }}">Блоги {{ $total['blog'] }}</a>
    <?php $active = ($type == 'inbox') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=inbox" class="badge badge-{{ $active }}">Приват {{ $total['inbox'] }}</a>
    <?php $active = ($type == 'wall') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=wall" class="badge badge-{{ $active }}">Стена {{ $total['wall'] }}</a>
    <?php $active = ($type == 'load') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=load" class="badge badge-{{ $active }}">Загрузки{{ $total['load'] }}</a>
        <br><br>

    @if ($records->isNotEmpty())
        @foreach ($records as $data)
            <div class="post">
                @if ($data->relate)
                    <div class="b">
                        <i class="fa fa-file-o"></i>
                        <b>{!! profile($data->relate->user) !!}</b>
                        <small>({{ dateFixed($data->relate->created_at, "d.m.y / H:i:s") }})</small>

                        <div class="float-right">
                            @if (is_admin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                            @endif
                        </div>
                    </div>
                    <div>{!! bbCode($data->relate->text) !!}</div>
                @else
                    <div class="b">
                        <i class="fa fa-file-o"></i> <b>Сообщение не найдено</b>

                        <div class="float-right">
                            @if (is_admin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    @if ($data['path'])
                        <a href="{{ $data['path'] }}">Перейти к сообщению</a><br>
                    @endif
                    Жалоба: {!! profile($data->user) !!} ({{ dateFixed($data['created_at']) }})
                </div>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Жалоб еще нет!') }}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
