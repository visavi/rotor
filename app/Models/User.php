<?php

namespace App\Models;

use Curl\Curl;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class User extends BaseModel
{
    const BOSS   = 'boss';   // Владелец
    const ADMIN  = 'admin';  // Админ
    const MODER  = 'moder';  // Модератор
    const EDITOR = 'editor'; // Редактор
    const USER   = 'user';   // Пользователь
    const PENDED = 'pended'; // Ожидающий
    const BANNED = 'banned'; // Забаненный

    /**
     * Администраторы
     */
    const ADMIN_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
    ];

    /**
     * Участники
     */
    const USER_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
        self::USER,
    ];

    /**
     * Все пользователи
     */
    const ALL_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
        self::USER,
        self::PENDED,
        self::BANNED,
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь с таблицей online
     */
    public function online()
    {
        return $this->belongsTo(Online::class, 'id', 'user_id')->withDefault();
    }

    /**
     * Возвращает последний бан
     */
    public function lastBan()
    {
        return $this->hasOne(Banhist::class, 'user_id', 'id')
            ->whereIn('type', ['ban', 'change'])
            ->orderBy('created_at', 'desc')
            ->withDefault();
    }

    /**
     * Возвращает заметку пользователя
     */
    public function note()
    {
        return $this->hasOne(Note::class)->withDefault();
    }

    /**
     * Возвращает ссылку на профиль пользователя
     *
     * @param  string  $color цвет логина
     * @param  boolean $link  выводить как ссылку
     * @return string        путь к профилю
     */
    public function getProfile($color = null, $link = true): string
    {
        if ($this->id) {
            $name = empty($this->name) ? $this->login : $this->name;

            if ($color) {
                $name = '<span style="color:' . $color . '">' . $name . '</span>';
            }

            if ($link) {
                return '<a class="author" href="/users/' . $this->login . '" data-login="' . $this->login . '">' . $name . '</a>';
            }

            return '<span class="author" data-login="' . $this->login . '">' . $name . '</span>';
        }

        return '<span class="author" data-login="' . setting('guestsuser') . '">' . setting('guestsuser') . '</span>';
    }

    /**
     * Возвращает пол пользователя
     *
     * @return string пол пользователя
     */
    public function getGender(): string
    {
        if ($this->gender === 'female') {
            return '<i class="fa fa-female fa-lg"></i>';
        }

        return '<i class="fa fa-male fa-lg"></i>';
    }

    /**
     * Авторизует пользователя
     *
     * @param  string  $login    Логин
     * @param  string  $password Пароль пользователя
     * @param  boolean $remember Запомнить пароль
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|bool
     */
    public static function auth($login, $password, $remember = true)
    {
        $domain = siteDomain(siteUrl());

        if (!empty($login) && !empty($password)) {

            $user = self::query()->where('login', $login)->first();

            if ($user && password_verify($password, $user['password'])) {

                if ($remember) {
                    setcookie('login', $user->login, strtotime('+1 year', SITETIME), '/', $domain);
                    setcookie('password', md5($user->password . env('APP_KEY')), strtotime('+1 year', SITETIME), '/', $domain, null, true);
                }

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY') . $user->password);

                // Сохранение привязки к соц. сетям
                if (! empty($_SESSION['social'])) {
                    Social::query()->create([
                        'user_id'    => $user->id,
                        'network'    => $_SESSION['social']->network,
                        'uid'        => $_SESSION['social']->uid,
                        'created_at' => SITETIME,
                    ]);
                }

                $authorization = Login::query()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::query()->create([
                        'user_id' => $user->id,
                        'ip' => getIp(),
                        'brow' => getBrowser(),
                        'created_at' => SITETIME,
                        'type' => 1,
                    ]);
                }

                $user->update([
                    'visits'     => DB::raw('visits + 1'),
                    'updated_at' => SITETIME
                ]);

                return $user;
            }
        }

        return false;
    }

    /**
     * Авторизует через социальные сети
     *
     * @param string $token идентификатор Ulogin
     * @return void
     * @throws \ErrorException
     */
    public static function socialAuth($token)
    {
        $domain = siteDomain(siteUrl());

        $curl = new Curl();
        $network = $curl->get('http://ulogin.ru/token.php',
            [
                'token' => $token,
                'host' => $_SERVER['HTTP_HOST']
            ]
        );

        if ($network && empty($network->error)) {
            $_SESSION['social'] = $network;

            $social = Social::query()
                ->where('network', $network->network)
                ->where('uid', $network->uid)
                ->first();

            if ($social && $user = getUserById($social->user_id)) {

                setcookie('login', $user->login, strtotime('+1 year', SITETIME), '/', $domain);
                setcookie('password', md5($user->password . env('APP_KEY')), strtotime('+1 year', SITETIME), '/', $domain, null, true);

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY') . $user->password);

                setFlash('success', 'Добро пожаловать, ' . $user->login . '!');
                redirect('/');
            }
        }
    }

    /**
     * Возвращает название уровня по ключу
     *
     * @param  string $level
     * @return string
     */
    public static function getLevelByKey(string $level): string
    {
        $levels = explode(',', setting('statusname'));

        switch ($level) {
            case self::BOSS:
                $status = $levels[0];
                break;
            case self::ADMIN:
                $status = $levels[1];
                break;
            case self::MODER:
                $status = $levels[2];
                break;
            case self::EDITOR:
                $status = $levels[3];
                break;
            case self::USER:
                $status = $levels[4];
                break;
            case self::PENDED:
                $status = $levels[5];
                break;
            case self::BANNED:
                $status = $levels[6];
                break;
            default: $status = setting('statusdef');
        }

        return $status;
    }

    /**
     * Возвращает уровень пользователя
     *
     * @return string Уровень пользователя
     */
    public function getLevel(): string
    {
        return self::getLevelByKey($this->level);
    }

    /**
     * Возвращает онлайн-статус пользователя
     *
     * @return string онлайн-статус
     */
    public function getOnline(): string
    {
        static $visits;

        $online = '<div class="online bg-danger" title="Оффлайн"></div>';

        if (! $visits) {
            if (@filemtime(STORAGE . '/temp/visit.dat') < time() - 10) {

                $onlines = Online::query()
                    ->whereNotNull('user_id')
                    ->pluck('user_id', 'user_id')
                    ->all();

                file_put_contents(STORAGE . '/temp/visit.dat', json_encode($onlines), LOCK_EX);
            }

            $visits = json_decode(file_get_contents(STORAGE . '/temp/visit.dat'));
        }

        if (isset($visits->{$this->id})) {
            $online = '<div class="online bg-success" title="Онлайн"></div>';
        }

        return $online;
    }

    /**
     * Возвращает статус пользователя
     *
     * @return string статус пользователя
     */
    public function getStatus(): string
    {
        static $status;

        if (! $this->id) {
            return setting('statusdef');
        }

        if (! $status) {
            $this->saveStatus(3600);
            $status = json_decode(file_get_contents(STORAGE . '/temp/status.dat'));
        }

        return $status->{$this->id} ?? setting('statusdef');
    }

    /**
     * Возвращает аватар для пользователя по умолчанию
     *
     * @return string код аватара
     */
    public function defaultAvatar(): string
    {
        $name   = empty($this->name) ? $this->login : $this->name;
        $color  = '#' . substr(dechex(crc32($this->login)), 0, 6);
        $letter = mb_strtoupper(utfSubstr($name, 0, 1), 'utf-8');

        return '<div class="avatar" style="background:' . $color . '"><a href="/users/' . $this->login . '">' . $letter . '</a></div>';
    }

    /**
     * Возвращает аватар пользователя
     *
     * @return string аватар пользователя
     */
    public function getAvatar(): string
    {
        if (! $this->id) {
            return '<img class="avatar" src="/assets/img/images/avatar_guest.png" alt=""> ';
        }

        if ($this->avatar && file_exists(UPLOADS . '/avatars/' . $this->avatar)) {
            return '<a href="/users/' . $this->login . '"><img src="/uploads/avatars/' . $this->avatar . '" alt="" class="avatar"></a> ';
        }

        return $this->defaultAvatar();
        //return '<a href="/users/' . $user->login . '"><img src="/assets/img/images/avatar_default.png" alt=""></a> ';
    }

    /**
     * Кеширует статусы пользователей
     *
     * @param  int $time время кеширования
     * @return void
     */
    public function saveStatus($time = 0): void
    {
        if (empty($time) || @filemtime(STORAGE . '/temp/status.dat') < time() - $time) {

            $users = self::query()
                ->select('users.id', 'users.status', 'status.name', 'status.color')
                ->leftJoin('status', function (JoinClause $join) {
                    $join->whereRaw('users.point between status.topoint and status.point');
                })
                ->where('users.point', '>', 0)
                ->get();

            $statuses = [];
            foreach ($users as $user) {

                if ($user->status) {
                    $statuses[$user->id] = '<span style="color:#ff0000">' . $user->status . '</span>';
                    continue;
                }

                if ($user->color) {
                    $statuses[$user->id] = '<span style="color:' . $user->color . '">' . $user->name . '</span>';
                    continue;
                }

                $statuses[$user->id] = $user->name;
            }

            file_put_contents(STORAGE . '/temp/status.dat', json_encode($statuses, JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
    }

    /**
     * Возвращает находится ли пользователь в контакатх
     *
     * @param  User $user объект пользователя
     * @return bool       находится ли в контактах
     */
    public function isContact(User $user): bool
    {
        $isContact = Contact::query()
            ->where('user_id', $this->id)
            ->where('contact_id', $user->id)
            ->first();

        if ($isContact) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает находится ли пользователь в игноре
     *
     * @param  User $user объект пользователя
     * @return bool       находится ли в игноре
     */
    public function isIgnore(User $user): bool
    {

        $isIgnore = Ignore::query()
            ->where('user_id', $this->id)
            ->where('ignore_id', $user->id)
            ->first();

        if ($isIgnore) {
            return true;
        }

        return false;
    }

    /**
     * Отправляет приватное сообщение
     *
     * @param  User|null $author Отправитель
     * @param  int       $text   текст сообщения
     * @return bool              результат отправки
     */
    public function sendMessage(?User $author, $text): bool
    {
        Inbox::query()->create([
            'user_id'    => $this->id,
            'author_id'  => $author ? $author->id : null,
            'text'       => $text,
            'created_at' => SITETIME,
        ]);

        $this->increment('newprivat');

        return true;
    }

    /**
     * Возвращает количество писем пользователя
     *
     * @return int количество писем
     */
    public function getCountMessages(): int
    {
        return Inbox::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает размер контакт-листа
     *
     * @return int количество контактов
     */
    public function getCountContact(): int
    {
        return Contact::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает размер игнор-листа
     *
     * @return int количество игнорируемых
     */
    public function getCountIgnore(): int
    {
        return Ignore::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает количество записей на стене сообщений
     *
     * @return int количество записей
     */
    public function getCountWall(): int
    {
        return Wall::query()->where('user_id', $this->id)->count();
    }

    /**
     * Удаляет альбом пользователя
     *
     * @return void
     */
    public function deleteAlbum(): void
    {
        $photos = Photo::query()->where('user_id', $this->id)->get();

        if ($photos->isNotEmpty()) {
            foreach ($photos as $photo) {
                $photo->comments()->delete();
                $photo->delete();
            }
        }
    }

    /**
     * Удаляет записи пользователя из всех таблиц
     *
     * @return bool       результат удаления
     * @throws \Exception
     */
    public function delete()
    {
        deleteFile(UPLOADS . '/pictures/' . $this->picture);
        deleteFile(UPLOADS . '/avatars/' . $this->avatar);

        Inbox::query()->where('user_id', $this->id)->delete();
        Outbox::query()->where('user_id', $this->id)->delete();
        Contact::query()->where('user_id', $this->id)->delete();
        Ignore::query()->where('user_id', $this->id)->delete();
        Rating::query()->where('user_id', $this->id)->delete();
        Wall::query()->where('user_id', $this->id)->delete();
        Note::query()->where('user_id', $this->id)->delete();
        Notebook::query()->where('user_id', $this->id)->delete();
        Banhist::query()->where('user_id', $this->id)->delete();
        Bookmark::query()->where('user_id', $this->id)->delete();
        Login::query()->where('user_id', $this->id)->delete();
        Invite::query()->where('user_id', $this->id)->orWhere('invite_user_id', $this->id)->delete();

        return parent::delete();
    }
}
