<!-- Menu -->
<nav id="menu">
    <h2>Меню</h2>

    <ul>
        <li><a href="/forum">Форум</a></li>
        <li><a href="/book">Гостевая</a></li>
        <li><a href="/news">Новости</a></li>
        <li><a href="/load">Скрипты</a></li>
        <li><a href="/blog">Блоги</a></li>


<?php if (is_user()): ?>
    <?php if (is_admin()): ?>

        <?php if (stats_spam()>0): ?>
            <li><a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a></li>
        <?php endif; ?>

        <?php if (App::user('newchat')<stats_newchat()): ?>
            <li><a href="/admin/chat"><span style="color:#ff0000">Чат</span></a></li>
        <?php endif; ?>

            <li><a href="/admin">Панель</a></li>
    <?php endif; ?>

    <li><a href="/menu">Меню</a></li>
    <li><a href="/logout">Выход</a></li>

<?php else: ?>
    <li><a href="/login">Авторизация</a></li>
    <li><a href="/register">Регистрация</a></li>
<?php endif; ?>



    </ul>
</nav>
<!-- Main -->
