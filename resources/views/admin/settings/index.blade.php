@extends('layout')

@section('title')
    Настройки сайта
@stop

@section('content')

    <h1>Настройки сайта</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Настройки сайта</li>
        </ol>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 bg-light p-1">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link" href="/admin/settings?act=main" id="main">Основные настройки</a>
                    <a class="nav-link" href="/admin/settings?act=mail" id="mail">Почта / Рассылка</a>
                    <a class="nav-link" href="/admin/settings?act=info" id="info">Вывод информации</a>
                    <a class="nav-link" href="/admin/settings?act=guest" id="guest">Гостевая / Новости</a>
                    <a class="nav-link" href="/admin/settings?act=forum" id="forum">Форум / Галерея</a>
                    <a class="nav-link" href="/admin/settings?act=bookmark" id="bookmark">Закладки / Голосования / Приват</a>
                    <a class="nav-link" href="/admin/settings?act=load" id="load">Загруз-центр</a>
                    <a class="nav-link" href="/admin/settings?act=blog" id="blog">Блоги</a>
                    <a class="nav-link" href="/admin/settings?act=page" id="page">Постраничная навигация</a>
                    <a class="nav-link" href="/admin/settings?act=other" id="other">Прочее / Другое</a>
                    <a class="nav-link" href="/admin/settings?act=protect" id="protect">Защита / Безопасность</a>
                    <a class="nav-link" href="/admin/settings?act=price" id="price">Стоимость и цены</a>
                    <a class="nav-link" href="/admin/settings?act=advert" id="advert">Реклама на сайте</a>
                    <a class="nav-link" href="/admin/settings?act=image" id="image">Загрузка изображений</a>
                    <a class="nav-link" href="/admin/settings?act=smile" id="smile">Смайлы</a>
                    <a class="nav-link" href="/admin/settings?act=offer" id="offer">Предложения / Проблемы</a>
                </div>
            </div>
            <div class="col-md-8">
                @include ('admin/settings/_' . $act)
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        $(function () {
            $('#{{ $act }}').tab('show');
        })
    </script>
@endpush
