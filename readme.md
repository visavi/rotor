# Rotor CMS

<p align="center">
  <img src="/public/assets/img/images/logo_big.png" alt="Rotor CMS" width="400">
</p>

<p align="center">
  <b>Lightweight modular CMS with a forum, blogs, gallery and file downloads.<br>
  Fast even on cheap shared hosting.</b>
</p>

<p align="center">
  <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/visavi/rotor" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/v/stable" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/downloads" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/visavi/rotor"><img src="https://poser.pugx.org/visavi/rotor/license" alt="License"></a>
  <a href="https://coveralls.io/github/visavi/rotor?branch=master"><img src="https://coveralls.io/repos/github/visavi/rotor/badge.svg?branch=master" alt="Coverage Status"></a>
</p>

<p align="center">
  <a href="https://github.com/visavi/rotor/blob/master/readme_ru.md">Описание на русском</a>
</p>

---

**Rotor** is an open source content management system written in PHP. It combines a community platform (forum, guestbook, wall, private messages) with classic site features (news, blogs, photo gallery, downloads, static pages) in a single lightweight package.

The engine is built on Laravel components but stays deliberately small: it installs in minutes, runs comfortably on inexpensive shared hosting, and keeps server load minimal even with a large audience.

## Screenshots

| Home page | Forum |
|---|---|
| ![Home](/public/assets/img/screenshots/home.png) | ![Forum](/public/assets/img/screenshots/forum.png) |

**Live example:** [visavi.net](https://visavi.net) — the developer community site, always running the latest version of Rotor.

## Features

- **Forum** — categories, topics, voting, moderation
- **Blogs and news** — articles with comments, categories, RSS
- **Photo gallery** — albums, comments, watermarks
- **Downloads** — file archive with categories and moderation
- **Community tools** — guestbook, user walls, private messages, boards (classifieds), mini-chat
- **Users** — registration, social auth, groups and permissions, ratings, custom profile fields
- **Administration** — full admin panel, page editor, style editor, backups, logs, spam/word filters
- **Modular architecture** — features ship as modules that can be installed, updated and disabled independently
- **Performance** — aggressive caching, minimal queries, works well without opcache tuning or dedicated servers
- **Themes** — customizable templates and styles

## Requirements

- PHP **8.3+**
- MySQL 5.7.8+, MariaDB 10.2.7+ or PostgreSQL 9.2+
- Any web server: Apache, Nginx, or the built-in PHP server

## Quick start

### One command (Composer)

```bash
composer create-project visavi/rotor .
```

Then open the site in a browser — the installer starts automatically and walks you through database setup and creating an admin account.

For the latest development version:

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

Open `http://localhost` and follow the installer.

### From an archive

1. Unpack the archive so that `public` is the web root (not required for Apache — the bundled `.htaccess` handles it)
2. Copy `.env.example` to `.env` and fill in the database credentials, admin login/email and mail settings
3. Make the directories inside `public/uploads`, `public/assets/modules`, `bootstrap/cache` and `storage` writable, or run `php artisan app:permission`
4. Open the site's main page — you will be redirected to the installer

### Manual installation

```bash
git clone https://github.com/visavi/rotor.git
cd rotor
cp .env.example .env        # fill in DB credentials, admin login/email, mail settings
composer install
php artisan migrate
php artisan db:seed
```

Create the database with utf8mb4 encoding beforehand:

```sql
CREATE DATABASE rotor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Local run without a web server

```bash
php artisan serve
```

Then open `http://localhost:8000`.

## Server configuration

### Cron

```
* * * * * php /path-to-site/artisan schedule:run >>/dev/null 2>&1
```

### Apache

Two layouts work on typical shared hosting:

1. **Default.** Put all files in `public_html`. The `.htaccess` in the site root redirects every request to the `public` directory, and the `.htaccess` inside `public` routes them to `index.php`.
2. **Split.** Put the engine one level above `public_html` and move the contents of `public` into `public_html`. Uncomment the corresponding code in `app/Providers/AppServiceProvider.php` to point the engine at the new public path. The root `.htaccess` can then be removed.

### Nginx

Add to the `server` section (strips trailing slashes and blocks direct PHP access in upload dirs):

```nginx
if (!-d $request_filename) {
    rewrite ^/(.*)/$ /$1 permanent;
}

location ~* /(assets|themes|uploads)/.*\.php$ {
    deny all;
}
```

In `location /` replace:

```nginx
try_files $uri $uri/ =404;
```

with:

```nginx
try_files $uri $uri/ /index.php?$query_string;
```

## Development

### Frontend assets (Vite)

```bash
npm ci
npm run build
```

### Migrations and seeding

```bash
php artisan migrate:status      # current migration status
php artisan migrate             # run migrations
php artisan migrate:rollback    # rollback the last batch
php artisan db:seed             # seed the database
```

### Production caching

With `APP_ENV=production` routes and configuration are cached automatically.

## Community & support

- **Forum:** [visavi.net](https://visavi.net/forums) — questions, modules, themes, announcements
- **Telegram:** [@visavi](https://t.me/visavi)
- **Issues:** [GitHub Issues](https://github.com/visavi/rotor/issues) — bug reports and feature requests

Contributions are welcome — fork the repository and open a pull request.

## Author

**Vantuz** — [visavi.net](https://visavi.net) · admin@visavi.net · Telegram [@visavi](https://t.me/visavi)

## License

Rotor is open-sourced software licensed under the [GPL-3.0 license](https://opensource.org/licenses/GPL-3.0).
