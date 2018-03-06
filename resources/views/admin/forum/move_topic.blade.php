@extends('layout')

@section('title')
    Перенос темы {{ $topic->title }}
@stop

@section('content')

    <h1>Перенос темы {{ $topic->title }}</h1>

    Автор темы: {!! profile($topic->user) !!}<br>
    Сообщений: {{ $topic->count_posts }}<br>
    Создан: {{ dateFixed($topic->created_at) }}<br>

    <div class="form mb-3">
        <form action="/admin/topic/move/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('fid') }}">
                <label for="fid">Выберите раздел для перемещения:</label>
                <select class="form-control" id="fid" name="fid">

                    @foreach ($forums as $data)
                        <option value="{{ $data->id }}"{{ $data->closed || $topic->forum_id == $data->id ? ' disabled' : '' }}>{{ $data->title }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ $datasub->closed || $topic->forum_id == $datasub->id ? ' disabled' : '' }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('fid') !!}
            </div>

            <button class="btn btn-primary">Перенести</button>
        </form>
    </div>

    <i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum/{{ $topic->forum_id }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Форум</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
