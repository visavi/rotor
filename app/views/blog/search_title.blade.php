@extends('layout')

@section('title')
    {{ $find }} - Результаты поиска - @parent
@stop

@section('content')

    <h1>Результаты поиска</h1>

    <h3>Поиск запроса &quot;{{ $find }}&quot; в заголовке</h3>
    Найдено совпадений: <b>{{ $page['total'] }}</b><br><br>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil"></i>
            <b><a href="/article/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({!! format_num($data['rating']) !!})
        </div>

        <div>
            Категория: <a href="/blog/{{ $data['category_id'] }}">{{ $data['name'] }}</a><br>
            Просмотров: {{ $data['visits'] }}<br>
            Автор: {!! profile($data['user']) !!}  ({{ date_fixed($data['created_at']) }})
        </div>
    @endforeach

    {{ App::pagination($page) }}

    {{ App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']) }}
    {{ App::view('includes/back', ['link' => '/blog/search', 'title' => 'Вернуться']) }}
@stop
