# AGENTS.md

Инструкции для ИИ-агентов, работающих с репозиторием Rotor CMS.

Rotor — модульная CMS на Laravel 13, PHP 8.3+. Ядро живёт в `app/`, разделы сайта (форум, блоги, галерея и т.д.) — модули в `modules/`.

## Окружение

Всё, что трогает БД, запускается в контейнере — с хоста `php artisan` не работает, MySQL живёт в docker:

```bash
docker compose exec rotor php artisan ...
docker compose exec rotor npm run build
```

Статические инструменты (pint) можно гонять и на хосте.

## Тесты и проверки

```bash
docker compose exec rotor php artisan route:clear             # кэш роутов ломает тесты модулей
docker compose exec rotor php artisan test tests/Feature/HomeControllerTest.php
docker compose exec rotor php artisan test --filter=testName
docker compose exec rotor php artisan test modules/Forum      # один модуль
docker compose exec rotor php artisan test                    # всё
docker compose exec rotor ./vendor/bin/pint --test <пути>
docker compose exec rotor ./vendor/bin/phpstan analyse <пути> --no-progress
```

От узкого к широкому: сначала затронутый файл или модуль, потом полный прогон. Pint и phpstan — по затронутым путям.

Чего не делать:

- `php artisan ... --env=testing` — без `.env.testing` Laravel молча берёт `.env` и бьёт по рабочей базе. Тестовое окружение задаёт `phpunit.xml`.
- `route:cache` / `config:cache` в dev — «Route ... not defined» в тестах модулей и уход тестов на рабочую базу. Увидел ошибку «Тесты подключены к базе «rotor»» — `config:clear`.
- Два прогона тестов одновременно — база `rotor_test` одна, гонка даёт ложное «Table 'rotor_test.…' doesn't exist».
- Записи в тестах создавать со всеми NOT NULL-полями: тесты идут в строгом MySQL (`DB_STRICT` в `phpunit.xml`), поле без значения и без `default` — ошибка 1364.
- `truncate()` и DDL в тестах — неявный COMMIT переживает откат `RefreshDatabase`. Использовать `delete()`.

Тест помечен risky «did not close its own output buffer» — однострочная `@section('name', $expr)` получила `null`. Завершать выражение `?? ''`.

`pint` по каталогу `bootstrap/` «чинит» генерённые `bootstrap/cache/*.php` — это не ошибка, их не коммитить.

## Модули

- Каталоги в `modules/` могут быть симлинками на соседний репозиторий `rotor-modules`. `grep -r` по `modules/` симлинки не обходит и молча даёт ноль — искать через `grep -R` или прямо в `rotor-modules`.
- Новый модуль — только через генератор: `php artisan make:module Name --model=Item --admin --api`. Копирование соседнего модуля теряет переводы, changelog и регистрацию модели.
- Вьюхи модуля адресуются `<snake_ключ>::<файл>` от корня `resources/views` модуля.
- Миграции модуля накатывает активация в `/admin/modules`. `artisan migrate` и `migrate:status` их не видят (`ModuleServiceProvider` не зовёт `loadMigrationsFrom`). В тестах пути подключает `tests/CreatesApplication.php`.
- Правки модуля записывать в его `changelog.md` в раздел `## Unreleased`. Версию в `module.php` не поднимать — бамп собирает архив релиза.
- Модуль выносится из ядра полным комплектом: настройки (контроллер, вид, миграция), переводы, хуки.
- Настройки модулей — только boss: `ModuleSettingController::getMiddleware()` добавляет `check.admin:boss` поверх маршрута (сохранение пишет любые `sets[]`, включая ключи ядра). Сигнатуру `update(Request)` не менять — модули её переопределяют.
- В шаблонах настроек модуля читать ключ как `$settings['key'] ?? ''` — иначе страница падает, если строки ещё нет.
- Языковые модули называются `Lang<Язык>` (`LangGerman`): все модули лежат в одном плоском `modules/`, префикс защищает от коллизии id.

## Миграции ядра

- `database/migrations/` — схема свежей установки.
- `database/upgrades/` — изменения для существующих сайтов. На свежей установке `InstallController` помечает их выполненными без запуска.

Изменение схемы = правка базовой `create_*`-миграции в `migrations/` + отдельный файл в `upgrades/`. Большие таблицы — отдельный upgrade-файл на таблицу.

## Соглашения по коду

- Движок работает в строгом MySQL (`DB_STRICT=true`): у счётчиков — `default(0)`, `upsert` с неполным набором колонок не использовать (`INSERT ... ON DUPLICATE KEY` требует все NOT NULL-поля) — массовое обновление через `update()` с `CASE`.
- Даты — `datetime`-колонки и Carbon. Новых int-таймстампов и `SITETIME` не заводить.
- Флеш-сообщения — `redirect()->with('success', ...)`. `setFlash()` — legacy, новый код на нём не писать.
- Публичные классы ядра — `App\Support\*` (`Registry`, `Hook`, `Validator`, `HtmlSanitizer`). `App\Classes\*` — deprecated-мост до 15.0; в модуле менять импорты попутно, когда он и так правится.
- Ajax — декларативно, атрибутами `data-ajax` в разметке (контракт — в `resources/js/ajax.js`). Ответ: `{success, message?, html?, redirect?}`. Своих `window.*`-обработчиков не писать: у модулей нет JS-бандла.
- HTML из редактора чистится кастом `App\Casts\HtmlCast` на уровне модели — контроллеры и `renderHtml` выглядят «незащищёнными», но защита на входе есть.
- Пустое поле формы приходит как `null` (`ConvertEmptyStringsToNull`), а колонки NOT NULL. Пользовательский текст — через каст: `HtmlCast` (HTML из редактора) или `TextCast` (заголовки); оба пишут `null` пустой строкой, где NULL значим — `::class . ':nullable'`. Значение из `$request->input()`, которое идёт в параметр `string`, кастить явно.
- Фильтры текста (`Registry::textFilter()`, модуль `Antimat`) применяются при чтении атрибута этими кастами — в базе оригинал. `antimat()` — deprecated до 15.0, в новом коде не звать.
- Ссылки наружу (платёжки, вебхуки, письма) — `url()` / `route()`. `asset()` при `ASSET_URL=/` даёт относительный путь.
- Письма — blade-компоненты: `resources/views/mailer/x.blade.php` + `mailer/text/x.blade.php`, стили только в `components/mail`. Markdown-mail не использовать: он портит логины и пароли с `_` и `*`.
- Глобальные middleware (сессия, `SetLocale`, `ApplyTheme`) в `bootstrap/app.php` не переносить в группу `web` — 404 вне групп начнёт падать в 500.
- CSS: сначала утилиты Bootstrap, потом общий переиспользуемый класс, правило под одну страницу — в крайнем случае.
- Фронтенд собирается Vite; `build.cssTarget` в `vite.config.mjs` держит старые `min-width`-медиазапросы для старых Safari и Android — не убирать.
