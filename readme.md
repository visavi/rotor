Rotor - mobile cms
=========

![](/public/assets/img/images/logo.png)  

[Description in Russian](https://github.com/visavi/rotor/blob/master/readme_ru.md)

[![Php Version](https://img.shields.io/packagist/php-v/visavi/rotor)](https://php.net)
[![Latest Stable Version](https://poser.pugx.org/visavi/rotor/v/stable)](https://packagist.org/packages/visavi/rotor)
[![Total Downloads](https://poser.pugx.org/visavi/rotor/downloads)](https://packagist.org/packages/visavi/rotor)
[![Latest Unstable Version](https://poser.pugx.org/visavi/rotor/v/unstable)](https://packagist.org/packages/visavi/rotor)
[![License](https://poser.pugx.org/visavi/rotor/license)](https://packagist.org/packages/visavi/rotor)
[![Code Climate](https://codeclimate.com/github/visavi/rotor/badges/gpa.svg)](https://codeclimate.com/github/visavi/rotor)
[![Coverage Status](https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master)](https://coveralls.io/github/visavi/rotor?branch=master)

Welcome!
We thank you for choosing to use our script for your site. Rotor mobile cms is a functionally complete open source content management system written in PHP. It uses a MySQL database to store the contents of your site.

**Rotor** is a flexible, powerful and intuitive system with minimal hosting requirements, a high level of protection and an excellent choice for building a website of any complexity.

The main feature of Rotor is low load on system resources and high speed, even with a very large audience of the site, the load on the server will be minimal, and you will not experience any problems with displaying information.

### Installing the Rotor engine (From the archive)

1. Configure the site so that `public` is the root directory (Not necessary for apache)

2. Unpack the archive

3. Set up the .env configuration file, environment, data for accessing the database, administrator login and email, and data for sending emails, sendmail or smtp.

4. Set write permissions to all directories inside `public/uploads`, `public/assets/modules`, `bootstrap/cache` and `storage`

5. Go to the main page of the site, you will be automatically transferred to the installer

6. Complete all installer conditions

### Installing the Rotor engine (From the repository)

1. Configure the site so that `public` is the root directory (Not necessary for apache)

2. Unpack the archive

3. Configure the .env configuration file, the environment, the data for accessing the database, the administrator's login and email, and the data for sending mail, sendmail or smtp. If you install CMS manually, then rename the configuration file .env.example to .env

4. Set write permissions to all directories inside `public/uploads`, `public/assets/modules`, `bootstrap/cache` and `storage` or execute the command `php artisan app:permission`

5. Install and configure the dependency manager [Composer](https://getcomposer.org).
   or you can download the finished package
    [composer.phar](https://getcomposer.org/composer.phar)
    and run it through the command
   `php composer.phar install`

6. Go to the site directory run the command in the console `composer install`

7. Create a database with utf8mb4 encoding and a user for it from the control panel on your server, during the installation of the script, you will need to enter this data to be connected to the .env file
`CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`  
   
8. Migrate using the console command `php artisan migrate`

9. Fill out the database using the command `php artisan db:seed`

### Installation by one command
To install the stable version, go to the site directory in the console and execute the command
```
composer create-project visavi/rotor .
```

To install the latest version, run the command
```
composer create-project --stability=dev visavi/rotor .
```

### Requirements

Minimal PHP version required for PHP 8.2, MySQL 5.7.8, MariaDB 10.2.7 or Postgres 9.2

### Migrations and database seeder

Current migration status `php artisan migrate:status`

Create migrations `php artisan make:migration CreateTestTable`

Performing migrations `php artisan migrate` or `php artisan migrate --path=/database/migrations/CreateTestTable.php` to migrate to a specific version

Rollback last migration `php artisan migrate:rollback` or `php artisan migrate:rollback --step=1` to rollback all migrations to a specific version

Create seeder `php 
artisan make:seeder UsersSeeder`

Performing seeder `php artisan db:seed` or `php artisan db:seed --class=UserSeeder` for a specific seed

### Caching Settings

If you set `APP_ENV = production`, then routes and project configuration settings will be cached

### Cron settings

```
* * * * * php /path-to-site/artisan schedule:run >>/dev/null 2>&1
```

### Apache settings

There are 2 ways to install the engine on a regular hosting

Default. All files are placed in the public_html directory. htaccess in the root of the site, redirects all requests to the public directory.

.htaccess inside public handles all requests and redirects them to index.php

If method 1 is not suitable or does not work well, then you can place all files on the same level as public_html, and transfer all files from public to public_html.

You also need to specify that public_html will be instead of the public directory, for this you need to uncomment the code in the `app/Providers/AppServiceProvider.php` file.

.htaccess at the root of the engine can be removed.

### Nginx settings

For the paths to be processed correctly, you need to configure the site.

Add the following entry to the server section:

```
if (!-d $request_filename) {
    rewrite ^/(.*)/$ /$1 permanent;
}

```
necessary to remove slashes at the end of the path and prohibit viewing php files

```
location ~* /(assets|themes|uploads)/.*\.php$ {
    deny all;
}
```
In the location / section, you must replace the line

```
try_files $uri $uri/ =404

to

try_files $uri $uri/ /index.php?$query_string;
```

### Run without Nginx

If there is no Nginx server on the local machine, it is sufficient to use the built-in PHP server through the console. To raise the server and access the system you need:

1. While in the console, go to the public folder
2. Run the command in the console `php -S localhost:8000` or `php artisan serve`
3. Enter the browser link localhost:8000

If, when the server starts, the console displays information that port 8000 is busy, try port 8080

### Webpack settings

To build css and js, you need to install npm and nodejs, then run the commands
```
npm ci
npm run build
```

### Author
Author: Vantuz  
Email: admin@visavi.net  
Site: https://visavi.net  
Telegram: @visavi  
Phone: +79167407574  

### License

The Rotor is open-sourced software licensed under the [GPL-3.0 license](http://opensource.org/licenses/GPL-3.0)
