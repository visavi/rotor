# AGENTS.md

Инструкции для ИИ-агентов, работающих с репозиторием Rotor CMS.

Rotor — модульная CMS на Laravel 13, PHP 8.3+. Ядро живёт в `app/`, разделы сайта (форум, блоги, галерея и т.д.) — модули в `modules/`.

## Окружение и проверки

MySQL живёт в docker, поэтому artisan, тесты, pint и phpstan — в контейнере. Фронтенд собирается на хосте: `npm run build`.

```bash
docker compose exec rotor php artisan test modules/Forum      # файл, --filter=..., модуль или без пути — всё
docker compose exec rotor composer pint                      # ядро и все модули; composer lint — только проверка
docker compose exec rotor ./vendor/bin/phpstan analyse <пути> --no-progress
```

От узкого к широкому: сначала затронутый файл или модуль, потом полный прогон; phpstan — по затронутым путям. Устаревшее API ловит phpstan, остальные запреты — `tests/Unit/ArchitectureTest.php`: сообщение упавшей проверки объясняет, что не так.

Тестовое окружение задаёт `phpunit.xml`. Не запускать `php artisan ... --env=testing`: без `.env.testing` Laravel молча берёт `.env` и бьёт по рабочей базе.

## Модули

- Каталоги в `modules/` — симлинки на соседний репозиторий `rotor-modules`: `grep -r` их не обходит и молча даёт ноль. Искать через `grep -R` или прямо в `rotor-modules`.
- Новый модуль — только генератором: `php artisan make:module Name --model=Item --admin --api`.
- Правки модуля — в его `changelog.md` под `## Unreleased`. `version` в `module.php` не поднимать: бамп собирает архив релиза. `requires` поднять, если модуль начал использовать новый класс или метод ядра.
- Модуль выносится из ядра полным комплектом: настройки, переводы, хуки.

## Схема ядра

`database/migrations/` — схема свежей установки, `database/upgrades/` — изменения для установленных сайтов (на свежей установке установщик помечает их выполненными). Изменение схемы = правка `create_*`-миграции в `migrations/` + отдельный файл в `upgrades/`.

## Код

- Готовое в ядре брать, а не писать своё: `App\Rules\TextLengthRule` (длина текста из редактора без тегов), `Admin\DeleteSelectedRequest` (удаление отмеченных `del[]`), `User\UserRules` (поля аккаунта), `FileService::rules()` и `attach()` (вложения), `User::chargeMoney()` (списание денег), касты `HtmlCast` и `TextCast` (пользовательский текст — в них чистка HTML и фильтры вывода).
- Ajax — атрибутами `data-ajax` в разметке, контракт в `resources/js/ajax.js`; ошибки валидации он показывает сам. Свой JS модулю можно (`@push('scripts')` или `resources/assets`), но без глобальных функций для `onclick`: блок показывает `data-bs-toggle="collapse"`, подтверждение — `data-confirm` на форме или кнопке, готовые действия (цитата, копирование, удаление вложения) — атрибутами из `resources/js/main.js`.
- CSS: сначала утилиты Bootstrap, потом общий переиспользуемый класс, правило под одну страницу — в крайнем случае.
