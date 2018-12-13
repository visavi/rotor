@extends('layout')

@section('title')
    Жалобы
@stop

@section('content')

    <h1>Жалобы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Жалобы</li>
        </ol>
    </nav>

    <?php $active = ($type === 'post') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=post" class="badge badge-{{ $active }}">Форум {{ $total['post'] }}</a>
    <?php $active = ($type === 'guest') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=guest" class="badge badge-{{ $active }}">Гостевая {{ $total['guest'] }}</a>
    <?php $active = ($type === 'message') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=message" class="badge badge-{{ $active }}">Приват {{ $total['message'] }}</a>
    <?php $active = ($type === 'wall') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=wall" class="badge badge-{{ $active }}">Стена {{ $total['wall'] }}</a>
    <?php $active = ($type === 'comment') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=comment" class="badge badge-{{ $active }}">Комментарии {{ $total['comment'] }}</a>
    <br><br>

    @if ($records->isNotEmpty())
        @foreach ($records as $data)
            <div class="post">
                @if ($data->relate)
                    <div class="b">
                        <i class="fa fa-file"></i>
                        <b>{!! $data->relate->author ? $data->relate->author->getProfile() : $data->relate->user->getProfile() !!}</b>
                        <small>({{ dateFixed($data->relate->created_at, "d.m.y / H:i:s") }})</small>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                    <div>{!! bbCode($data->relate->text) !!}</div>
                @else
                    <div class="b">
                        <i class="fa fa-file"></i> <b>Сообщение не найдено</b>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    @if ($data['path'])
                        <a href="{{ $data->path }}">Перейти к сообщению</a><br>
                    @endif
                    Жалоба: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Жалоб еще нет!') !!}
    @endif
@stop
