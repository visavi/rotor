Rotor 7.0
=========

[![Php Version](https://img.shields.io/badge/php-%3E%3D%207.1.3-brightgreen.svg)](https://php.net)
[![Latest Stable Version](https://poser.pugx.org/visavi/rotor/v/stable)](https://packagist.org/packages/visavi/rotor)
[![Total Downloads](https://poser.pugx.org/visavi/rotor/downloads)](https://packagist.org/packages/visavi/rotor)
[![Latest Unstable Version](https://poser.pugx.org/visavi/rotor/v/unstable)](https://packagist.org/packages/visavi/rotor)
[![License](https://poser.pugx.org/visavi/rotor/license)](https://packagist.org/packages/visavi/rotor)
[![Build Status](https://travis-ci.org/visavi/rotor.svg)](https://travis-ci.org/visavi/rotor)
[![Dependency Status](https://www.versioneye.com/user/projects/588f6be5760ce60041d80429/badge.svg)](https://www.versioneye.com/user/projects/588f6be5760ce60041d80429)
[![Code Climate](https://codeclimate.com/github/visavi/rotor/badges/gpa.svg)](https://codeclimate.com/github/visavi/rotor)
[![Coverage Status](https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master)](https://coveralls.io/github/visavi/rotor?branch=master)

Добро пожаловать!
Мы благодарим Вас за то, что Вы решили использовать наш скрипт для своего сайта. Rotor - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта.

**Rotor** является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности

Главной особенностью Rotor является низкая нагрузка на системные ресурсы и высокая скорость работы, даже при очень большой аудитории сайта нагрузка на сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.

### Действия при первой установке движка Rotor

1. Настройте сайт так чтобы `public` был корневой директорией

2. Распакуйте архив

3. Установите и настройте менеджер зависимостей [Composer](https://getcomposer.org).
   или можно скачать готовый пакет 
    [composer.phar](https://getcomposer.org/composer.phar)
    и запустить его через команду
   `php composer.phar install`

4. Перейдите в директорию с сайтом выполните команду в консоли `composer install`

5. Создайте базу данных с кодировкой utf8mb4 и пользователя для нее из панели управления на вашем сервере, во время установки скрипта необходимо будет вписать эти данные для соединения в файл .env
`CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`  

6. Настройте конфигурационный файл .env, окружение, данные для доступа к БД, логин и email администратора и данные для отправки писем, sendmail или smtp. Если устанавливаете CMS вручную, то переименуйте конфигурационный файл .env.example в .env (Файл не отслеживается git'ом, поэтому на сервере и на локальном сайте могут находиться 2 разных файла с разными окружениями указанными в APP_ENV)

7. Установите права на запись всем директориям внутри `public/uploads` и `storage` или выполните команду `php rotor install`

8. Если вы впервые устанавливаете движок, то необходимо очистить папку /database/upgrades и настройка APP_NEW должна быть true, если вы обновляетесь с предыдущей версии, то APP_NEW должна быть false.

9. Выполните миграции с помощью консольной команды `php rotor migrate`

10. Выполните заполнение БД с помощью команды `php rotor seed:run`

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

Минимальная версия PHP необходимая для работы движка PHP 7.1.3 и MySQL 5.5.3

Если версия MySQL ниже 5.5.3, то необходимо в файле .env уставновить
`DB_COLLATION=utf8_unicode_ci`

Для работы полнотекстового поиска рекомендуется версия MySQL >= 5.6

### Миграции и заполнение БД

Текущий статус миграции `php rotor status`

Создание миграций `php rotor create CreateTestTable`

Выполнение миграций `php rotor migrate` или `php rotor migrate -t 20110103081132` для отдельной миграции

Откат последней миграции `php rotor rollback` или `php rotor rollback -t 20120103083322` для отдельной миграции

Создание сида `php rotor seed:create UsersSeeder`

Выполнение сида `php rotor seed:run` или `php rotor seed:run -s UsersSeeder` для отдельного сида

### Настройки cron

```
* * * * * php path-to-site/app/cron.php 2>&1
```

### Настройки nginx

Чтобы пути обрабатывались правильно необходимо настроить сайт

В секцию server добавить следующую запись: 

```
if (!-d $request_filename) {
    rewrite ^/(.*)/$ /$1 permanent;
}

```
необходимую для удаление слешей в конце пути и запрета просмотра php файлов

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
2. Выполнить в консоли команду `php -S localhost:8000`
3. Зайти в браузере по ссылке localhost:8000

Если при запуске сервера консоль выводит информацию о том, что порт 8000 занят, попробуйте порт 8080

### Author
Author: Vantuz  
Email: admin@visavi.net  
Site: http://visavi.net  
Skype: vantuzilla  
Phone: +79167407574  

### License

The Rotor is open-sourced software licensed under the [GPL-3.0 license](http://opensource.org/licenses/GPL-3.0)
