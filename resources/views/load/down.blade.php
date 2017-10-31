@extends('layout')

@section('title')
    {{ $down->title }}
@stop

@section('description', stripString($down->text))

@section('content')

    <h1>{{ $down->title }}</h1>

    @if (isAdmin('admin'))
        <a href="/admin/load?act=editdown&amp;cid={{ $down->category_id }}&amp;id={{ $down->id }}">Редактировать</a> /
        <a href="/admin/load?act=movedown&amp;cid={{ $down->category_id }}&amp;id={{ $down->id }}">Переместить</a>
    @endif

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/load">Категории</a></li>

        @if ($down->category->parent)
            <li class="breadcrumb-item"><a href="/load/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/load/{{ $down->category_id }}">{{ $down->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $down->title }}</li>
        <li class="breadcrumb-item"><a href="/down/{{ $down->id }}/rss">RSS-лента</a></li>
    </ol>

    @if (! $down->active && $down->user_id == getUser('id'))
        <div class="info">
            <b>Внимание!</b> Данная загрузка добавлена, но еще требует модераторской проверки<br>
            <i class="fa fa-pencil"></i> <a href="/load/add?act=view&amp;id={{ $down->id }}">Перейти к редактированию</a>
        </div><br>
    @endif

    @if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
        <a href="/uploads/files/{{ $folder }}{{ $down->link }}" class="gallery">{{ resizeImage('uploads/files/'.$folder, $down->link, setting('previewsize'), ['alt' => $down->title]) }}</a><br>
    @endif

    <div class="message">{!! bbCode($down->text) !!}</div><br>

    <?php $poster = ''; ?>
    @if ($down->screen && file_exists(UPLOADS.'/screen/'.$folder.$down->screen))
        <?php $poster = ' poster="/uploads/screen/'.$folder.$down->screen.'"'; ?>

        @if ($ext != 'mp4')
            Скриншот:<br>
            <a href="/uploads/screen/{{ $folder }}{{ $down->screen }}" class="gallery">{{ resizeImage('uploads/screen/'.$folder, $down->screen, setting('previewsize'), ['alt' => $down->title]) }}</a><br><br>
        @endif
    @endif

    Добавлено: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})<hr>

    @if ($down->link && file_exists(UPLOADS.'/files/'.$folder.$down->link))

        @if ($ext == 'mp3' || $ext == 'mp4')

            @if ($ext == 'mp3')
                <audio src="/uploads/files/{{ $folder }}{{ $down->link }}"></audio><br/>
            @endif

            @if ($ext == 'mp4')
                <video width="640" height="360" style="width: 100%; height: 100%;" src="/uploads/files/{{ $folder }}{{ $down->link }}" {!! $poster !!}></video>
            @endif
        @endif

        @if ($ext == 'zip')
            <i class="fa fa-archive"></i> <b><a href="/down/{{ $down->id }}/zip">Просмотреть архив</a></b><br>
        @endif

        @if (getUser())
            <i class="fa fa-download"></i> <b><a href="/down/{{ $down->id }}/download">Скачать</a></b> ({{ $filesize }})<br>
        @else
            <div class="form">
                <label for="protect">Проверочный код:</label><br>
                <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" style="cursor: pointer;" alt=""><br>

            <form class="form-inline" action="/down/{{ $down->id }}/download" method="post">
                <input class="form-control" id="protect" name="protect" size="6" maxlength="6">
                <button class="btn btn-primary">Скачать ({{ $filesize }})</button>
            </form>
            <em>Чтобы не вводить код при каждом скачивании, советуем <a href="/register">зарегистрироваться</a></em></div><br>
        @endif

        <i class="fa fa-comment"></i> <b><a href="/down/{{ $down->id }}/comments">Комментарии</a></b> ({{ $down->comments }})
        <a href="/down/{{ $down->id }}/end">&raquo;</a><br>

        <br>Рейтинг: {!! ratingVote($rating) !!}<br>
        Всего голосов: <b>{{ $down->rated }}</b><br><br>
        Всего скачиваний: <b>{{ $down->loads }}</b><br>

        @if (getUser())
            <form class="form-inline" action="/down/{{ $down->id }}/vote/" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <select class="form-control" name="score">
                    <option value="5">Отлично</option>
                    <option value="4">Хорошо</option>
                    <option value="3">Нормально</option>
                    <option value="2">Плохо</option>
                    <option value="1">Отстой</option>
                </select>
                <button class="btn btn-primary">Оценить</button>
            </form>

            <br><label for="text">Скопировать адрес:</label><br>
            <input class="form-control" name="text" id="text" value="{{ siteUrl(true) }}/uploads/files/{{ $folder }}{{ $down->link }}"><br>
        @endif
    @else
        {{ showError('Файл еще не загружен!') }}
    @endif
@stop
