# Board Module Extraction — Design

**Date:** 2026-05-16  
**Scope:** Перенос функционала объявлений (boards) из ядра rotor в отдельный модуль `Board`

---

## Цель

Вынести boards в `rotor-modules/Board/` по образцу существующих модулей (Payment, Game и др.). Модуль самодостаточен: при отключении ядро не ломается.

---

## Структура модуля

```
rotor-modules/Board/
├── module.php
├── hooks.php
├── routes.php
├── Controllers/
│   ├── BoardController.php
│   └── Admin/
│       ├── BoardController.php
│       └── BoardSettingController.php
├── Console/
│   └── BoardDeactivation.php
├── Models/
│   ├── Board.php
│   └── Item.php
├── migrations/
│   └── 2018_05_28_164107_create_boards_table.php
└── resources/
    ├── lang/
    │   ├── ru/boards.php
    │   ├── ua/boards.php
    │   └── en/boards.php
    └── views/
        ├── boards/          (index, view, create, edit, active)
        ├── admin/
        │   ├── boards/      (index, edit, edit_item, categories)
        │   └── settings/    (_boards.blade.php)
        ├── feeds/
        │   └── _boards.blade.php
        ├── search/
        │   └── _items.blade.php
        └── widgets/
            └── _boards.blade.php
```

---

## module.php — capabilities

```php
return [
    'name'        => 'Объявления',
    'description' => 'Доска объявлений',
    'version'     => '1.0.0',
    'author'      => 'Vantuz',
    'email'       => 'admin@visavi.net',
    'homepage'    => 'https://visavi.net',

    // Регистрация в morph map
    'morphs' => [
        \Modules\Board\Models\Item::class,
    ],

    // Регистрация в поиске: morphName => label
    'searchable' => [
        \Modules\Board\Models\Item::class => 'board::boards.boards_section',
    ],

    // Консольные команды
    'commands' => [
        \Modules\Board\Console\BoardDeactivation::class,
    ],

    // Типы для фида
    'feedTypes' => [
        'items' => [
            'class' => \Modules\Board\Models\Item::class,
            'withs' => ['user', 'files', 'category.parent'],
        ],
    ],

    // Ссылки в панели управления модулями
    'panel' => [
        '/admin/board-settings' => 'board::boards.settings',
    ],
];
```

---

## Интеграция через ModuleServiceProvider

`ModuleServiceProvider::boot()` расширяется: читает capabilities из `module.php` каждого активного модуля и регистрирует:

```php
// Morph map
foreach ($module['morphs'] ?? [] as $class) {
    Relation::morphMap([$class::$morphName => $class]);
}

// Searchable types
foreach ($module['searchable'] ?? [] as $class => $label) {
    Search::$extraTypes[$class::$morphName] = $label;
}

// Feed types
foreach ($module['feedTypes'] ?? [] as $key => $config) {
    Feed::$types[$key] = $config;
}

// Console commands
if ($this->app->runningInConsole()) {
    $this->commands($module['commands'] ?? []);
}
```

`hooks.php` модуля добавляет директорию вьюх в глобальный поиск:
```php
view()->addLocation(base_path('modules/Board/resources/views'));
```
Это позволяет `@includeIf('search/_items')` и `@include('feeds/_boards')` находить вьюхи модуля без изменения blade-файлов ядра.

---

## Переводы

Неймспейс модуля: `board`. `ModuleServiceProvider` уже вызывает `loadTranslationsFrom`.

Все вызовы в файлах модуля:
- `__('boards.key')` → `__('board::boards.key')`
- Вьюхи настроек: `__('settings.boards_*')` → `__('board::boards.settings_*')`

В ядре остаются нетронутыми: `__('index.boards')` (используется в Search, виджетах).

---

## Настройки

Модуль несёт собственную страницу `/admin/board-settings`:

- `Admin/BoardSettingController::index()` — рендерит `board::admin.settings._boards`
- `Admin/BoardSettingController::update()` — валидирует, пишет в таблицу `settings`
- Все `setting('boards_*')` и `setting('board_*')` в контроллерах модуля **не меняются**

---

## Изменения в ядре

| Файл | Действие |
|------|----------|
| `app/Providers/ModuleServiceProvider.php` | + регистрация morphs, commands, feedTypes, searchable |
| `app/Providers/MorphMapServiceProvider.php` | − строка `Item::$morphName => Item::class` |
| `app/Classes/Feed.php` | `private array $types` → `public static array $types` |
| `app/Models/Search.php` | + `public static array $extraTypes = []`; `getRelateTypes()` мержит его |
| `app/Models/Setting.php` | − `'boards'` из `getActions()` |
| `routes/web.php` | − boards-роуты |
| `routes/admin.php` | − boards-роуты |
| `resources/views/feeds/_feed.blade.php` | `@include('feeds/_boards')` → `@includeIf('feeds/_boards')` |
| `resources/views/widgets/_classic.blade.php` | ссылка на `route('boards.index')` в `@if(Route::has('boards.index'))` |
| `app/Console/Commands/BoardDeactivation.php` | удалить |
| `resources/lang/{ru,ua,en}/boards.php` | удалить |
| `resources/views/boards/` | удалить |
| `resources/views/admin/boards/` | удалить |
| `resources/views/admin/settings/_boards.blade.php` | удалить |
| `resources/views/feeds/_boards.blade.php` | удалить |
| `resources/views/widgets/_boards.blade.php` | удалить |

---

## Что НЕ меняется в ядре

- `resources/views/search/index.blade.php` — уже использует `@includeIf` динамически
- `resources/views/widgets/_classic.blade.php` — только обёртка `Route::has()`
- Все вызовы `setting()` в ядре не касаются boards
- Таблицы БД: `boards`, `items` — остаются, миграция переезжает в модуль

---

## Порядок реализации

1. Создать структуру модуля, перенести файлы
2. Обновить неймспейсы (`App\Models\Board` → `Modules\Board\Models\Board` и т.д.)
3. Обновить вызовы переводов в файлах модуля
4. Создать `BoardSettingController`
5. Расширить `ModuleServiceProvider` (morphs, commands, feedTypes, searchable)
6. Внести минимальные изменения в ядро (таблица выше)
7. Проверить: фиды, поиск, настройки, консольная команда, морф-маппинг
