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
        <li class="breadcrumb-item"><a href="/down/rss/{{ $down->id }}">RSS-лента</a></li>
    </ol>

    @if (! $down->active && $down->user_id == getUser('id'))
        <div class="info">
            <b>Внимание!</b> Данная загрузка добавлена, но еще требует модераторской проверки<br>
            <i class="fa fa-pencil-alt"></i> <a href="/load/add?act=view&amp;id={{ $down->id }}">Перейти к редактированию</a>
        </div><br>
    @endif

    @if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
        <a href="/uploads/files/{{ $down->folder }}{{ $down->link }}" class="gallery">{{ resizeImage('uploads/files/'.$down->folder, $down->link, ['alt' => $down->title]) }}</a><br>
    @endif

    <div class="message">
        {!! bbCode($down->text) !!}
    </div><br>

    @if ($down->files->isNotEmpty())
        @foreach ($down->files as $screen)
            <a href="/uploads/screen/{{ $down->folder }}{{ $screen->hash }}" class="gallery">{!! resizeImage('uploads/screen/'.$down->folder, $screen->hash, ['alt' => $down->title]) !!}</a>
        @endforeach
    @endif
    <br>

    <?php $poster = ''; ?>
    @if ($down->screen && file_exists(UPLOADS.'/screen/'.$down->folder.$down->screen))
        <?php $poster = ' poster="/uploads/screen/'.$down->folder.$down->screen.'"'; ?>

        @if ($ext != 'mp4')
            Скриншот:<br>
            <a href="/uploads/screen/{{ $down->folder }}{{ $down->screen }}" class="gallery">{{ resizeImage('uploads/screen/'.$down->folder, $down->screen, ['alt' => $down->title]) }}</a><br><br>
        @endif
    @endif

    Добавлено: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})<hr>

    @if ($down->link && file_exists(UPLOADS.'/files/'.$down->folder.$down->link))

        @if ($ext == 'mp3' || $ext == 'mp4')

            @if ($ext == 'mp3')
                <audio src="/uploads/files/{{ $down->folder }}{{ $down->link }}"></audio><br/>
            @endif

            @if ($ext == 'mp4')
                <video width="640" height="360" style="width: 100%; height: 100%;" src="/uploads/files/{{ $down->folder }}{{ $down->link }}" {!! $poster !!}></video>
            @endif
        @endif

        @if ($ext == 'zip')
            <i class="fa fa-archive"></i> <b><a href="/down/zip/{{ $down->id }}">Просмотреть архив</a></b><br>
        @endif

        @if (getUser())
            <i class="fa fa-download"></i> <b><a href="/down/download/{{ $down->id }}">Скачать</a></b> ({{ $filesize }})<br>
        @else
            <div class="form">
                <label for="protect">Проверочный код:</label><br>
                <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" style="cursor: pointer;" alt=""><br>

            <form class="form-inline" action="/down/download/{{ $down->id }}" method="post">
                <div class="form-group{{ hasError('protect') }}">
                    <input class="form-control" id="protect" name="protect" size="6" maxlength="6" required>
                    <button class="btn btn-primary">Скачать ({{ $filesize }})</button>
                </div>
            </form>
            {!! textError('protect') !!}

            <em>Чтобы не вводить код при каждом скачивании, советуем <a href="/register">зарегистрироваться</a></em></div><br>
        @endif

        <i class="fa fa-comment"></i> <b><a href="/down/comments/{{ $down->id }}">Комментарии</a></b> ({{ $down->comments }})
        <a href="/down/end/{{ $down->id }}">&raquo;</a><br>

        <br>Рейтинг: {!! ratingVote($rating) !!}<br>
        Всего голосов: <b>{{ $down->rated }}</b><br>
        Всего скачиваний: <b>{{ $down->loads }}</b><br><br>

        @if (getUser() && getUser('id') != $down->user_id)

            <label for="score">Проверочный код:</label><br>
            <form class="form-inline" action="/down/vote/{{ $down->id }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('score') }}">
                    <select class="form-control" id="score" name="score">
                        <option value="5" {{ $down->vote == 5 ? ' selected' : '' }}>Отлично</option>
                        <option value="4" {{ $down->vote == 4 ? ' selected' : '' }}>Хорошо</option>
                        <option value="3" {{ $down->vote == 3 ? ' selected' : '' }}>Нормально</option>
                        <option value="2" {{ $down->vote == 2 ? ' selected' : '' }}>Плохо</option>
                        <option value="1" {{ $down->vote == 1 ? ' selected' : '' }}>Отстой</option>
                    </select>
                    {!! textError('protect') !!}
                </div>
                <button class="btn btn-primary">Оценить</button>
            </form>

            {{--<br><label for="text">Скопировать адрес:</label><br>
            <input class="form-control" name="text" id="text" value="{{ siteUrl(true) }}/uploads/files/{{ $down->folder }}{{ $down->link }}"><br>--}}
        @endif
    @else
        {!! showError('Файл еще не загружен!') !!}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
