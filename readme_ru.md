# Rotor CMS

<p align="center">
  <img src="/public/assets/img/images/logo_big.png" alt="Rotor CMS" width="400">
</p>

<p align="center">
  <b>Лёгкая модульная CMS с форумом, блогами, галереей и загрузками.<br>
  Быстрая даже на дешёвом shared-хостинге.</b>
</p>

<p align="center">
  <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/visavi/rotor" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/v/stable" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/license" alt="License"></a>
  <a href="https://coveralls.io/github/visavi/rotor?branch=master"><img src="https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master" alt="Coverage Status"></a>
</p>

<p align="center">
  <a href="https://github.com/visavi/rotor/blob/master/readme.md">Description in English</a>
</p>

---

**Rotor** — система управления контентом с открытым исходным кодом, написанная на PHP. Она объединяет платформу для сообщества (форум, гостевая, стена, личные сообщения) и классические возможности сайта (новости, блоги, фотогалерея, загрузки, статические страницы) в одном лёгком пакете.

Движок построен на компонентах Laravel, но остаётся сознательно компактным: устанавливается за минуты, комфортно работает на недорогом shared-хостинге и создаёт минимальную нагрузку на сервер даже при большой аудитории.

## Скриншоты

| Главная страница | Форум |
|---|---|
| ![Главная](/public/assets/img/screenshots/home.png) | ![Форум](/public/assets/img/screenshots/forum.png) |

**Живой пример:** [visavi.net](https://visavi.net) — сайт сообщества разработчиков, всегда работающий на последней версии Rotor.

## Возможности

- **Форум** — категории, темы, голосования, модерация
- **Блоги и новости** — статьи с комментариями, категории, RSS
- **Фотогалерея** — альбомы, комментарии, водяные знаки
- **Загрузки** — файловый архив с категориями и модерацией
- **Сообщество** — гостевая книга, стены пользователей, личные сообщения, доска объявлений, мини-чат
- **Пользователи** — регистрация, вход через соцсети, группы и права, рейтинги, дополнительные поля профиля
- **Администрирование** — полноценная админ-панель, редактор страниц, редактор стилей, бэкапы, логи, антимат и спам-фильтры
- **Модульная архитектура** — возможности поставляются модулями, которые можно устанавливать, обновлять и отключать независимо
- **Производительность** — агрессивное кеширование, минимум запросов, отлично работает без тюнинга opcache и выделенных серверов
- **Темы** — настраиваемые шаблоны и стили

## Требования

- PHP **8.3+**
- MySQL 5.7.8+, MariaDB 10.2.7+ или PostgreSQL 9.2+
- Любой веб-сервер: Apache, Nginx или встроенный сервер PHP

## Быстрый старт

### Одной командой (Composer)

```bash
composer create-project visavi/rotor .
```

Затем откройте сайт в браузере — автоматически запустится установщик, который проведёт через настройку базы данных и создание учётной записи администратора.

Для установки последней dev-версии:

```bash
composer create-project --stability=dev visavi/rotor .
```

### Docker (Laravel Sail)

```bash
git clone https://github.com/visavi/rotor.git
cd rotor
cp .env.example .env
composer install
./vendor/bin/sail up -d
```

Откройте `http://localhost` и следуйте установщику.

### Из архива

1. Распакуйте архив так, чтобы `public` был корневой директорией веб-сервера (для Apache не обязательно — входящий в комплект `.htaccess` сделает это сам)
2. Скопируйте `.env.example` в `.env` и заполните данные доступа к БД, логин/email администратора и настройки почты
3. Установите права на запись директориям внутри `public/uploads`, `public/assets/modules`, `bootstrap/cache` и `storage` или выполните `php artisan app:permission`
4. Откройте главную страницу сайта — вас автоматически перенаправит на установщик

### Ручная установка

```bash
git clone https://github.com/visavi/rotor.git
cd rotor
cp .env.example .env        # заполните данные БД, логин/email администратора, настройки почты
composer install
php artisan migrate
php artisan db:seed
```

Предварительно создайте базу данных с кодировкой utf8mb4:

```sql
CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Локальный запуск без веб-сервера

```bash
php artisan serve
```

Затем откройте `http://localhost:8000`.

## Настройка сервера

### Cron

```
* * * * * php /path-to-site/artisan schedule:run >>/dev/null 2>&1
```

### Apache

На обычном хостинге работают два варианта размещения:

1. **По умолчанию.** Все файлы размещаются в `public_html`. `.htaccess` в корне сайта перенаправляет все запросы в директорию `public`, а `.htaccess` внутри `public` направляет их на `index.php`.
2. **Раздельный.** Движок размещается на уровень выше `public_html`, а содержимое `public` переносится в `public_html`. Раскомментируйте соответствующий код в `app/Providers/AppServiceProvider.php`, чтобы указать движку новый публичный путь. `.htaccess` в корне после этого можно удалить.

### Nginx

Добавьте в секцию `server` (убирает слеши в конце пути и запрещает прямой доступ к PHP в директориях загрузок):

```nginx
if (!-d $request_filename) {
    rewrite ^/(.*)/$ /$1 permanent;
}

location ~* /(assets|themes|uploads)/.*\.php$ {
    deny all;
}
```

В секции `location /` замените:

```nginx
try_files $uri $uri/ =404;
```

на:

```nginx
try_files $uri $uri/ /index.php?$query_string;
```

## Разработка

### Сборка фронтенда (Vite)

```bash
npm ci
npm run build
```

### Миграции и заполнение БД

```bash
php artisan migrate:status      # текущий статус миграций
php artisan migrate             # выполнить миграции
php artisan migrate:rollback    # откатить последнюю партию
php artisan db:seed             # заполнить базу данных
```

### Кеширование в продакшене

При `APP_ENV=production` роуты и конфигурация кешируются автоматически.

## Сообщество и поддержка

- **Форум:** [visavi.net](https://visavi.net/forums) — вопросы, модули, темы, анонсы
- **Telegram:** [@visavi](https://t.me/visavi)
- **Issues:** [GitHub Issues](https://github.com/visavi/rotor/issues) — баг-репорты и предложения

Вклад приветствуется — форкните репозиторий и откройте pull request.

## Автор

**Vantuz** — [visavi.net](https://visavi.net) · admin@visavi.net · Telegram [@visavi](https://t.me/visavi)

## Лицензия

Rotor — программное обеспечение с открытым исходным кодом под лицензией [GPL-3.0](https://opensource.org/licenses/GPL-3.0).
