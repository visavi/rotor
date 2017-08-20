@extends('layout')

@section('title')
    Публикация новой статьи - @parent
@stop

@section('content')

    <h1>Публикация новой статьи</h1>

    <a href="/blog">Блоги</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blog?act=blogs">Все статьи</a><hr>

    <div class="form next">
        <form action="/blog/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Категория*:<br>
            <select name="cid">
                <option value="0">Выберите категорию</option>
                @foreach ($cats as $key => $value)
                    <?php $selected = ($cid == $key) ? ' selected="selected"' : ''; ?>
                    <option value="{{ $key }}"{{ $selected }}>{{ $value }}</option>
                @endforeach
            </select><br>

            Заголовок:<br>
            <input name="title" size="50" maxlength="50"><br>
            Текст:<br>
            <textarea id="markItUp" cols="25" rows="10" name="text"></textarea><br>
            Метки:<br>
            <input name="tags" size="50" maxlength="100"><br>

            <button class="btn btn-primary">Опубликовать</button>
        </form>
    </div><br>

    Рекомендация! Для разбивки статьи по страницам используйте тег [nextpage]<br>
    Метки статьи должны быть от 2 до 20 символов с общей длиной не более 50 символов<br><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
@stop
