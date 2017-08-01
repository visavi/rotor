<?php if (Request::ajax()): ?>
    <?php $message = check(Request::input('data')); ?>
    <!doctype html>

    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>RotorCMS</title>
        <?= include_style() ?>
        <?= include_javascript() ?>
    </head>
    <body>
        <?php exit(App::bbCode($message)); ?>
    </body>
    </html>
<?php endif; ?>
