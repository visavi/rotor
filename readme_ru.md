Rotor - mobile cms
=========

![](/public/assets/img/images/logo.png) 

[Description in English](https://github.com/visavi/rotor/blob/master/readme.md)

[![Php Version](https://img.shields.io/packagist/php-v/visavi/rotor)](https://php.net)
[![Latest Stable Version](https://poser.pugx.org/visavi/rotor/v/stable)](https://packagist.org/packages/visavi/rotor)
[![Total Downloads](https://poser.pugx.org/visavi/rotor/downloads)](https://packagist.org/packages/visavi/rotor)
[![Latest Unstable Version](https://poser.pugx.org/visavi/rotor/v/unstable)](https://packagist.org/packages/visavi/rotor)
[![License](https://poser.pugx.org/visavi/rotor/license)](https://packagist.org/packages/visavi/rotor)
[![Build Status](https://www.travis-ci.com/visavi/rotor.svg?branch=master)](https://www.travis-ci.com/github/visavi/rotor)
[![Code Climate](https://codeclimate.com/github/visavi/rotor/badges/gpa.svg)](https://codeclimate.com/github/visavi/rotor)
[![Coverage Status](https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master)](https://coveralls.io/github/visavi/rotor?branch=master)

Добро пожаловать!
Мы благодарим Вас за то, что Вы решили использовать наш скрипт для своего сайта. Rotor mobile cms - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта.

**Rotor** является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности

Главной особенностью Rotor является низкая нагрузка на системные ресурсы и высокая скорость работы, даже при очень большой аудитории сайта нагрузка на сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.

### Установка движка Rotor (Из архива)

1. Настройте сайт так чтобы `public` был корневой директорией (Не обязательно для apache)

2. Распакуйте архив

3. Настройте конфигурационный файл .env, окружение, данные для доступа к БД, логин и email администратора и данные для отправки писем, sendmail или smtp.
    
4. Установите права на запись всем директориям внутри `public/uploads`, `public/assets/modules`, `bootstrap/cache` и `storage`
   
5. Перейдите на главную страницу сайта, вас автоматически перекинет на установщик

6. Выполните все условия установщика

###  Установка движка Rotor (Из репозитория)

1. Настройте сайт так чтобы `public` был корневой директорией (Не обязательно для apache)

2. Распакуйте архив
   
3. Настройте конфигурационный файл .env, окружение, данные для доступа к БД, логин и email администратора и данные для отправки писем, sendmail или smtp. Если устанавливаете CMS вручную, то переименуйте конфигурационный файл .env.example в .env

4. Установите права на запись всем директориям внутри `public/uploads`, `public/assets/modules`, `bootstrap/cache` и `storage` или выполните команду `php artisan app:permission`
   
5. Установите и настройте менеджер зависимостей [Composer](https://getcomposer.org).
   или можно скачать готовый пакет 
    [composer.phar](https://getcomposer.org/composer.phar)
    и запустить его через команду
   `php composer.phar install`

6. Перейдите в директорию с сайтом выполните команду в консоли `composer install`

7. Создайте базу данных с кодировкой utf8mb4 и пользователя для нее из панели управления на вашем сервере, во время установки скрипта необходимо будет вписать эти данные для соединения в файл .env
`CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`  
   
8. Выполните миграции с помощью консольной команды `php artisan migrate`

9. Выполните заполнение БД с помощью команды `php artisan db:seed`

### Установка одной командой
Для установки стабильной версии перейдите в консоли в директорию с сайтом и выполните команду 
```
composer create-project visavi/rotor .
```

Для установки последней версии выполните команду
```
composer create-project --stability=dev visavi/rotor .
```

### Требования

Минимальная версия PHP необходимая для работы движка PHP 8.0.2, MySQL 5.7.8, MariaDB 10.2.7 или Postgres 9.2 

### Миграции и заполнение БД

Текущий статус миграции `php artisan migrate:status`

Создание миграций `php artisan make:migration CreateTestTable`

Выполнение миграций `php artisan migrate` или `php artisan migrate --path=/database/migrations/CreateTestTable.php` для выполнения до определенной миграции

Откат последней миграции `php artisan migrate:rollback` или `php artisan migrate:rollback --step=1` для отката до определенной миграции

Создание сида `php artisan make:seeder UsersSeeder`

Выполнение сида `php artisan db:seed` или `php artisan db:seed --class=UserSeeder` для отдельного сида

### Кеширование настроек

Если установить `APP_ENV=production`, то будут кешироваться роуты и настройки конфигурации проекта

### Настройки cron

```
* * * * * php /path-to-site/artisan schedule:run >>/dev/null 2>&1
```

### Настройки apache

Существует 2 способа установки движка на обычном хостинге

1. По умолчанию. Все файлы размещаются в директории public_html. htaccess в корне сайта, перенаправляет все запросы на директорию public. htaccess внутри public обрабатывает все запросы и перенаправляет их к index.php

2. Если 1 способ не подходит или работает плохо, то можно разместить все файлы на одном уровне с public_html, а все файлы из public перенести в public_html. Также необходимо указать, чтобы public_html, для этого нужно расскоментировать код в файле `app/Providers/AppServiceProvider.php`. htaccess в корне движка можно удалить.


### Настройки nginx

Чтобы пути обрабатывались правильно необходимо настроить сайт

В секцию server добавить следующую запись: 

```
if (!-d $request_filename) {
    rewrite ^/(.*)/$ /$1 permanent;
}

```
Необходимую для удаления слешей в конце пути и запрета просмотра php файлов

```
location ~* /(assets|themes|uploads)/.*\.php$ {
    deny all;
}
```
В секции location / необходимо заменить строку

```
try_files $uri $uri/ =404

на

try_files $uri $uri/ /index.php?$query_string;
```

### Запуск без Nginx

В случае отсутствия сервера Nginx на локальной машине достаточно использовать встроенный сервер PHP через консоль. Для поднятия сервера и доступа к системе нужно:

1. Находясь в консоли, перейти в папку public
2. Выполнить в консоли команду `php -S localhost:8000` или `php artisan serve`
3. Зайти в браузере по ссылке localhost:8000

Если при запуске сервера консоль выводит информацию о том, что порт 8000 занят, попробуйте порт 8080

### Настройки webpack

Для сборки css и js необходимо установить npm и nodejs, после этого запустить команды
```
npm ci
npm run prod
```

### Author
Author: Vantuz  
Email: admin@visavi.net  
Site: https://visavi.net  
Skype: vantuzilla  
Phone: +79167407574  

### License

The Rotor is open-sourced software licensed under the [GPL-3.0 license](http://opensource.org/licenses/GPL-3.0)
