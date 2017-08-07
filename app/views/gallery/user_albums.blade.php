@extends('layout')

@section('title')
    Список всех фотографий {{ $user->login }} (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Список всех фотографий {{ $user->login }}</h1>

    @if ($photos->isNotEmpty())

        @foreach ($photos as $data)
            <div class="b">
                <i class="fa fa-picture-o"></i>
                <b><a href="/gallery/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ read_file(HOME.'/uploads/pictures/'.$data['link']) }})<br />

                @if ($moder)
                    <a href="/gallery/{{ $data['id'] }}/edit?page={{ $page['current'] }}">Редактировать</a> /
                    <a href="/gallery/{{ $data['id'] }}/delete?page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a>
                @endif
            </div>
            <div>
                <a href="/gallery/{{ $data['id'] }}">{!! resize_image('uploads/pictures/', $data['link'], Setting::get('previewsize'), ['alt' => $data['title']]) !!}</a><br />

                @if ($data['text'])
                   {{ App::bbCode($data['text']) }}<br />
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ date_fixed($data['created_at']) }})<br />
                <a href="/gallery/{{ $data['id'] }}/comments">Комментарии</a> ({{ $data['comments'] }})
            </div>
        @endforeach

        {{ App::pagination($page) }}

        Всего фотографий: <b>{{ $page['total'] }}</b><br /><br />
    @else
        {{ show_error('Фотографий в альбоме еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/albums">Альбомы</a><br />
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
@stop
