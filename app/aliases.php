<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Мост совместимости App\Classes -> App\Support
|--------------------------------------------------------------------------
|
| Классы, на которые опираются модули, переехали из App\Classes в App\Support.
| Модули, собранные под старое имя, продолжают работать: автозагрузчик ловит
| обращение к App\Classes\* и подставляет класс из App\Support.
|
| @deprecated Мост держится до 15.0, затем удаляется вместе с этим файлом.
|
*/

spl_autoload_register(static function (string $class): void {
    if (! str_starts_with($class, 'App\\Classes\\')) {
        return;
    }

    $target = 'App\\Support\\' . substr($class, strlen('App\\Classes\\'));

    if (class_exists($target)) {
        class_alias($target, $class);
    }
});

/*
| Проверяя тип аргумента, PHP автозагрузчик не зовёт: объект другого класса —
| сразу TypeError. Поэтому ленивого моста мало там, где ядро передаёт готовый
| объект в колбэк модуля (`Registry::onProfileValidate` и подобные) — если
| колбэк объявлен со старым именем, вызов упадёт. Такие классы объявляем сразу.
*/
class_alias(App\Support\Validator::class, 'App\Classes\Validator');
