@extends('layout')

@section('title')
    {{ $photo->title }}
@stop

@section('content')

    <h1>{{ $photo->title }}</h1>

    <ol class="breadcrumb">
        @if (isAdmin())
            <li class="breadcrumb-item"><a href="/admin/gallery?act=edit&amp;gid={{ $photo->id }}">Редактировать</a></li>
            <li class="breadcrumb-item"><a href="/admin/gallery?act=del&amp;del={{ $photo->id }}&amp;uid={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a></li>
        @endif

        @if ($photo->user->id == getUser('id') && ! isAdmin())
            <li class="breadcrumb-item"><a href="/gallery/{{ $photo->id }}/edit">Редактировать</a></li>
            <li class="breadcrumb-item"><a href="/gallery/{{ $photo->id }}/delete?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a></li>
        @endif
    </ol>

    <div>
        <a href="/uploads/pictures/{{ $photo->link }}" class="gallery"><img  class="img-fluid" src="/uploads/pictures/{{ $photo->link }}" alt="image"></a><br>

        @if ($photo->text)
            {!! bbCode($photo->text) !!}<br>
        @endif

        <div class="js-rating">Рейтинг:
            @unless (getUser('id') == $photo->user_id)
                <a class="post-rating-down<?= $photo->vote == '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ App\Models\Photo::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
            @endunless
            <span>{!! formatNum($photo->rating) !!}</span>
            @unless (getUser('id') == $photo->user_id)
                <a class="post-rating-up<?= $photo->vote == '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ App\Models\Photo::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
            @endunless
        </div>

        Размер: {{ formatFileSize(UPLOADS.'/pictures/'.$photo->link) }}<br>
        Добавлено: {!! profile($photo->user) !!} ({{ dateFixed($photo->time) }})<br>
        <a href="/gallery/{{ $photo->id }}/comments">Комментарии</a> ({{ $photo->comments }})
        <a href="/gallery/{{ $photo->id }}/end">&raquo;</a>
    </div>
    <br>

    <?php $nav = photoNavigation($photo->id); ?>

    @if ($nav['next'] || $nav['prev'])
        <div class="form" style="text-align:center">
            @if ($nav['next'])
                <a href="/gallery/{{ $nav['next'] }}">&laquo; Назад</a> &nbsp;
            @endif

            @if ($nav['prev'])
                &nbsp; <a href="/gallery/{{ $nav['prev'] }}">Вперед &raquo;</a>
            @endif
        </div>
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album/{{ $photo->user->login }}">В альбом</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
