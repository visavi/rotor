@extends('layout')

@section('title')
    {{ $topic->title }} (Стр. {{ $page->current }})
@stop

@section('description', 'Обсуждение темы: '.$topic->title.' (Стр. '.$page->current.')')

@section('content')
    <h1>{{ $topic->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/forum">Форум</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forum/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forum/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>

    @if ($topic->curators)
       <div>
            <span class="badge badge-warning">
                <i class="fa fa-wrench"></i> Кураторы темы:
                @foreach ($topic->curators as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{!! profile($curator) !!}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="p-1 bg-info text-white">{!! bbCode($topic->note) !!}</div>
    @endif

    <hr>

    @if ($topic->closed)
        <a href="/admin/topic/action/{{ $topic->id }}?type=open&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">Открыть</a> /
    @else
        <a href="/admin/topic/action/{{ $topic->id }}?type=closed&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">Закрыть</a> /
    @endif

    @if ($topic->locked)
        <a href="/admin/topic/action/{{ $topic->id }}?type=unlocked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">Открепить</a> /
    @else
        <a href="/admin/topic/action/{{ $topic->id }}?type=locked&amp;page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}">Закрепить</a> /
    @endif

    <a href="/admin/topic/edit/{{ $topic->id }}">Изменить</a> /
    <a href="/admin/topic/move/{{ $topic->id }}">Переместить</a> /
    <a href="/admin/topic/delete/{{ $topic->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
    <a href="/topic/{{ $topic->id }}?page={{ $page->current }}">Обзор</a><br>


    @if ($vote)
        <h3>{{ $vote->title }}</h3>

        @if (!getUser() || $vote->poll || $vote->closed)
            @foreach ($vote->voted as $key => $data)
                <?php $proc = round(($data * 100) / $vote->sum, 1); ?>
                <?php $maxproc = round(($data * 100) / $vote->max); ?>

                <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
                {!! progressBar($maxproc, $proc.'%') !!}
            @endforeach
        @else
            <form action="/topic/vote/{{ $topic->id }}?page={{ $page->current }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                @foreach ($vote->answers as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                @endforeach
                <br><button class="btn btn-sm btn-primary">Голосовать</button>
            </form><br>
        @endif

        Всего проголосовало: {{ $vote->count }}
    @endif

    <form action="/admin/post/delete?tid={{ $topic->id }}&amp;page={{ $page->current }}" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form text-right">
            <label for="all">Отметить все</label>
            <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
        </div>

        @if ($posts->isNotEmpty())
            @foreach ($posts as $data)
                <?php $num = ($page->offset + $loop->iteration ); ?>
                <div class="post">
                    <div class="b" id="post_{{ $data->id }}">
                        <div class="float-right text-right">
                            @if (getUser())
                                @if (getUser('id') != $data->user_id)
                                    <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                                    <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                                    <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Post::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                                @endif

                                <a href="/admin/post/edit/{{ $data->id }}?page={{ $page->current }}" title="Редактировать"><i class="fa fa-pencil-alt text-muted"></i></a>

                                <input type="checkbox" name="del[]" value="{{ $data->id }}">
                            @endif

                            <div class="js-rating">
                                @if (getUser() && getUser('id') != $data->user_id)
                                    <a class="post-rating-down{{ $data->vote == '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ App\Models\Post::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-minus"></i></a>
                                @endif
                                <span>{!! formatNum($data->rating) !!}</span>
                                @if (getUser() && getUser('id') != $data->user_id)
                                    <a class="post-rating-up{{ $data->vote == '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ App\Models\Post::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-plus"></i></a>
                                @endif
                            </div>
                        </div>

                        <div class="img">{!! userAvatar($data->user) !!}</div>

                        {{ $num }}. <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                    </div>

                    <div class="message">
                        {!! bbCode($data->text) !!}
                    </div>

                    @if ($data->files->isNotEmpty())
                        <div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br>
                        @foreach ($data->files as $file)
                            <?php $ext = getExtension($file->hash); ?>

                            {!! icons($ext) !!}
                            <a href="/uploads/forum/{{ $topic->id }}/{{ $file->hash }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                            @if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png']))
                                <a href="/uploads/forum/{{ $topic->id }}/{{ $file->hash }}" class="gallery" data-group="{{ $data->id }}">{!! resizeImage('/uploads/forum/' . $topic->id.'/'.$file->hash, ['alt' => $file->name]) !!}</a><br>
                            @endif
                        @endforeach
                        </div>
                    @endif

                    @if ($data->edit_user_id)
                        <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: {{ $data->editUser->login }} ({{ dateFixed($data->updated_at) }})</small><br>
                    @endif

                    @if (isAdmin())
                        <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                    @endif
                </div>
            @endforeach

        @else
            {!! showError('Сообщений еще нет, будь первым!') !!}
        @endif

        <span class="float-right">
            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </span>
    </form>

    {!! pagination($page) !!}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="form">
                <form action="/topic/create/{{ $topic->id }}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">Сообщение:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    @if (getUser('point') >= setting('forumloadpoints'))
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-secondary" for="inputFile">
                                <input id="inputFile" type="file" name="file" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                                Файл&hellip;
                            </label>
                            <span class="badge badge-info" id="upload-file-info"></span>

                            <div class="p-1 bg-info text-white">
                                Максимальный вес файла: <b>{{ round(setting('forumloadsize') / 1024 / 1024) }}</b> Mb<br>
                                Допустимые расширения: {{ str_replace(',', ', ', setting('forumextload')) }}
                            </div><br>
                        </div>

                        <span class="imgright js-attach-button">
                            <a href="#" onclick="return showAttachForm();">Загрузить файл</a>
                        </span>
                    @endif

                    <button class="btn btn-primary">Написать</button>
                </form>
            </div><br>

        @else
            {!! showError('Данная тема закрыта для обсуждения!') !!}
        @endif
    @else
        {!! showError('Для добавления сообщения необходимо авторизоваться') !!}
    @endif

    <a href="/smiles">Смайлы</a>  /
    <a href="/tags">Теги</a>  /
    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid={{ $topic->forum_id }}">Поиск</a><br>
@stop
