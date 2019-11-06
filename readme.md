Rotor 
=========

[Description in Russian](https://github.com/visavi/rotor/blob/master/readme_ru.md)

[![Php Version](https://img.shields.io/badge/php-%3E%3D%207.2.0-brightgreen.svg)](https://php.net)
[![Latest Stable Version](https://poser.pugx.org/visavi/rotor/v/stable)](https://packagist.org/packages/visavi/rotor)
[![Total Downloads](https://poser.pugx.org/visavi/rotor/downloads)](https://packagist.org/packages/visavi/rotor)
[![Latest Unstable Version](https://poser.pugx.org/visavi/rotor/v/unstable)](https://packagist.org/packages/visavi/rotor)
[![License](https://poser.pugx.org/visavi/rotor/license)](https://packagist.org/packages/visavi/rotor)
[![Build Status](https://travis-ci.org/visavi/rotor.svg)](https://travis-ci.org/visavi/rotor)
[![Code Climate](https://codeclimate.com/github/visavi/rotor/badges/gpa.svg)](https://codeclimate.com/github/visavi/rotor)
[![Coverage Status](https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master)](https://coveralls.io/github/visavi/rotor?branch=master)

Welcome!
We thank you for choosing to use our script for your site. Rotor is a functionally complete open source content management system written in PHP. It uses a MySQL database to store the contents of your site.

**Rotor** is a flexible, powerful and intuitive system with minimal hosting requirements, a high level of protection and an excellent choice for building a website of any complexity.

The main feature of Rotor is low load on system resources and high speed, even with a very large audience of the site, the load on the server will be minimal, and you will not experience any problems with displaying information.

### Actions at the first installation of the Rotor engine

1. Configure the site so that `public` is the root directory (Not necessary for apache)
   If your site is in the public_html directory, then the contents of the public directory from the archive must be put in public_html, and everything else must be on the same level as public_html
     In app / bootstrap.php and change the HOME constant
 `define('HOME', BASEDIR . '/public_html');`

2. Unpack the archive

3. Install and configure the dependency manager [Composer](https://getcomposer.org).
   or you can download the finished package
    [composer.phar](https://getcomposer.org/composer.phar)
    and run it through the command
   `php composer.phar install`

4. Go to the site directory run the command in the console `composer install`

5. Create a database with utf8mb4 encoding and a user for it from the control panel on your server, during the installation of the script, you will need to enter this data to be connected to the .env file
`CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`  

6. Configure the .env configuration file, the environment, the data for accessing the database, the administrator's login and email, and the data for sending mail, sendmail or smtp. If you install CMS manually, then rename the configuration file .env.example to .env (The file is not tracked by git, so there can be 2 different files on the server and on the local site with different environments specified in APP_ENV)

7. Set write permissions to all directories inside `public / uploads` and` storage` or execute the command `php rotor app:configure`

8. If you are installing the engine for the first time, then you need to clear the / database / upgrades folder and the APP_NEW setting should be true; if you are upgrading from a previous version, then APP_NEW should be false.

9. Migrate using the console command `php rotor migrate`

10. Fill out the database using the command `php rotor seed:run`

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

Minimal PHP version required for PHP 7.2.0 and MySQL 5.5.3 engine

If MySQL version is lower than 5.5.3, then it is necessary to install in the .env file
`DB_COLLATION=utf8_unicode_ci`

If you use the InnoDB data storage type, then for full-text search, version MySQL >= 5.6

Storage type can be set to .env
`DB_ENGINE=InnoDB`

### Migrations and database seeder

Current migration status `php rotor status`

Create migrations `php rotor create CreateTestTable`

Performing migrations `php rotor migrate` or `php rotor migrate -t 20110103081132` for a specific migration

Rollback last migration `php rotor rollback` or `php rotor rollback -t 20120103083322` for a specific migration

Create seeder `php rotor seed:create UsersSeeder`

Performing seeder `php rotor seed:run` or `php rotor seed:run -s UsersSeeder` for a specific seed

### Caching Settings

If you set `APP_ENV = production`, then routes and project configuration settings will be cached

### Cron settings

```
* * * * * php /path-to-site/app/cron.php 2>&1
```

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
2. Run the command in the console `php -S localhost:8000`
3. Enter the browser link localhost:8000

If, when the server starts, the console displays information that port 8000 is busy, try port 8080

### Webpack settings

To compress css and js, you need to install npm and nodejs, then run the commands (Optional)
```
npm install
npm run prod
```

### Author
Author: Vantuz  
Email: admin@visavi.net  
Site: http://visavi.net  
Skype: vantuzilla  
Phone: +79167407574  

### License

The Rotor is open-sourced software licensed under the [GPL-3.0 license](http://opensource.org/licenses/GPL-3.0)
