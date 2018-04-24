@extends('layout')

@section('title')
    {{ $photo->title }}
@stop

@section('content')

    <h1>{{ $photo->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item"><a href="/gallery/album/{{ $photo->user->login }}">Альбом</a></li>
            <li class="breadcrumb-item active">{{ $photo->title }}</li>
        </ol>
    </nav>

    @if (isAdmin())
        <a href="/admin/gallery/edit/{{ $photo->id }}">Редактировать</a> /
       <a href="/admin/gallery/delete/{{ $photo->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a>
    @endif

    @if ($photo->user->id == getUser('id') && ! isAdmin())
        <a href="/gallery/edit/{{ $photo->id }}">Редактировать</a> /
        <a href="/gallery/delete/{{ $photo->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a>
    @endif

    <div>
        @foreach ($photo->files as $file)
            <a href="/uploads/pictures/{{ $file->hash }}" class="gallery" data-group="{{ $photo->id }}"><img  class="img-fluid" src="/uploads/pictures/{{ $file->hash }}" alt="image"></a><br>
        @endforeach

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

        Добавлено: {!! profile($photo->user) !!} ({{ dateFixed($photo->created_at) }})<br>
        <a href="/gallery/comments/{{ $photo->id }}">Комментарии</a> ({{ $photo->count_comments }})
        <a href="/gallery/end/{{ $photo->id }}">&raquo;</a>
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
@stop
