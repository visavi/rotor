@extends('layout')

@section('title', 'Собственные страницы сайта')

@section('header')
    <h1>Как создать свои страницы</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Как создать свои страницы</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-list-ol"></i> Быстрый старт</div>

        <div class="section-body">
            <ol class="mb-0">
                <li>Перейдите в директорию <code>/resources/views/files</code> — она автоматически генерирует страницы сайта</li>
                <li>Создайте в ней директорию с произвольным латинским названием (к примеру <code>library</code>)</li>
                <li>Положите в созданную директорию обычный файл с расширением <code>.blade.php</code> (к примеру <code>index.blade.php</code>)</li>
                <li>Напишите любой текст на этой странице, это может быть как html код, так и php</li>
                <li>Теперь попробуйте перейти на созданную страницу, введите в браузере <code>{{ config('app.url') }}/files/library</code></li>
                <li>Если страница отобразилась, значит вы все сделали правильно</li>
            </ol>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fa fa-exclamation-circle"></i>
        Все страницы сайта можно создавать, редактировать и удалять прямо из админки — для этого есть модуль
        «Редактор файлов»
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-folder-tree"></i> Имена файлов</div>

        <div class="section-body text-muted">
            В одной директории может быть неограниченное число файлов, расширение указывать не нужно — только имя папки
            и имя файла через слеш, к примеру <code>/library/simplepage</code>. Адрес <code>/library/index</code> равнозначен
            просто <code>/library</code>.
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-heading"></i> Заголовок страницы</div>

        <div class="section-body">
            <p>Заголовок подставляется в блок <code>title</code> и в название страницы <code>&lt;h1&gt;</code>:</p>

<pre class="code">
@@section('title', 'Новый заголовок страницы')
</pre>

            <p class="mt-3">Чтобы название отличалось от заголовка, задайте блок <code>header</code>:</p>

<pre class="code">
@@section('header')
    &lt;h1&gt;Измененное название страницы&lt;/h1&gt;
@@stop
</pre>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-diagram-project"></i> Навигация</div>

        <div class="section-body">
<pre class="code">
@@section('breadcrumb')
    &lt;nav>
        &lt;ol class="breadcrumb">
            &lt;li class="breadcrumb-item">&lt;a href="/">&lt;i class="fas fa-home">&lt;/i>&lt;/a>&lt;li>
            &lt;li class="breadcrumb-item">&lt;a href="/files">Файлы&lt;/a>&lt;/li>
            &lt;li class="breadcrumb-item active">Активная страница&lt;/li>
        &lt;/ol>
    &lt;/nav>
@@stop
</pre>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-align-left"></i> Описание</div>

        <div class="section-body">
            <p>Произвольное описание заполняет переменную <code>setting('description')</code>:</p>

<pre class="code">
@@section('description', 'Описание страницы')
</pre>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-folder-open"></i> Второй способ — раздел /pages</div>

        <div class="section-body">
            <p>
                Кроме <code>/files</code> страницы отдаёт раздел <code>/pages</code> — он забирает файлы из директории
                <code>/resources/views/main</code>. Так устроена сама страница «Информация»: файл
                <code>main/index.blade.php</code> открывается по адресу <code>{{ config('app.url') }}/pages</code>.
            </p>

            <p class="mb-0">Чтобы добавить свою страницу, положите файл в эту директорию:</p>

<pre class="code">
/resources/views/main/about.blade.php  →  {{ config('app.url') }}/pages/about
</pre>

            <p class="mt-3 mb-0">Разница между разделами:</p>

            <ul class="mb-0">
                <li><code>/files</code> — поддерживает вложенные директории, адрес может быть любой глубины</li>
                <li><code>/pages</code> — файлы лежат одним списком, имя только из латинских букв, цифр, дефиса и подчёркивания</li>
            </ul>

            <p class="text-muted mt-3 mb-0">
                Блоки <code>title</code>, <code>header</code>, <code>breadcrumb</code> и <code>description</code>
                работают в обоих разделах одинаково.
            </p>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title"><i class="fas fa-shield-halved"></i> Чтобы страницы пережили обновление</div>

        <div class="section-body">
            <p>
                Директория <code>/resources/custom</code> не затрагивается при обновлении движка, и вьюхи из неё
                перекрывают файлы ядра. Кладите свои страницы туда — адреса остаются теми же:
            </p>

<pre class="code">
/resources/custom/views/files/library/index.blade.php  →  {{ config('app.url') }}/files/library
/resources/custom/views/main/about.blade.php           →  {{ config('app.url') }}/pages/about
</pre>

            <p class="text-muted mt-3 mb-0">
                Тем же способом переопределяются страницы ядра: файл
                <code>custom/views/main/index.blade.php</code> заменит страницу «Информация», а оригинал останется нетронутым.
            </p>
        </div>
    </div>

    <a class="btn btn-primary mb-3" href="/files/example"><i class="fas fa-eye"></i> Посмотреть пример страницы</a>
@stop
