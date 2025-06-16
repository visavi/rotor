<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use App\Traits\SortableTrait;
use App\Traits\UploadTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Class User
 *
 * @property int    $id
 * @property string $login
 * @property string $password
 * @property string $email
 * @property string $level
 * @property string $name
 * @property string $country
 * @property string $city
 * @property string $language
 * @property string $info
 * @property string $site
 * @property string $phone
 * @property string $gender
 * @property string $birthday
 * @property int    $visits
 * @property int    $newprivat
 * @property int    $newwall
 * @property int    $allforum
 * @property int    $allguest
 * @property int    $allcomments
 * @property string $themes
 * @property string $timezone
 * @property int    $point
 * @property int    $money
 * @property string $status
 * @property string $color
 * @property string $avatar
 * @property string $picture
 * @property int    $rating
 * @property int    $posrating
 * @property int    $negrating
 * @property string keypasswd
 * @property int    $timepasswd
 * @property int    $sendprivatmail
 * @property int    $timebonus
 * @property string $confirmregkey
 * @property int    $newchat
 * @property int    $notify
 * @property string $apikey
 * @property string $subscribe
 * @property int    $timeban
 * @property int    $updated_at
 * @property int    $created_at
 * @property-read Collection<UserData> $data
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasFactory;
    use MustVerifyEmail;
    use Notifiable;
    use SearchableTrait;
    use SortableTrait;
    use UploadTrait;

    public const BOSS = 'boss';   // Владелец
    public const ADMIN = 'admin';  // Админ
    public const MODER = 'moder';  // Модератор
    public const EDITOR = 'editor'; // Редактор
    public const USER = 'user';   // Пользователь
    public const PENDED = 'pended'; // Ожидающий
    public const BANNED = 'banned'; // Забаненный

    /**
     * Администраторы
     */
    public const ADMIN_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
    ];

    /**
     * Участники
     */
    public const USER_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
        self::USER,
    ];

    /**
     * Все пользователи
     */
    public const ALL_GROUPS = [
        self::BOSS,
        self::ADMIN,
        self::MODER,
        self::EDITOR,
        self::USER,
        self::PENDED,
        self::BANNED,
    ];

    /**
     * Genders
     */
    public const MALE = 'male';
    public const FEMALE = 'female';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/pictures';

    /**
     * Директория загрузки аватаров
     */
    public string $uploadAvatarPath = '/uploads/avatars';

    /**
     * Morph name
     */
    public static string $morphName = 'users';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['login', 'name', 'info', 'site', 'status'];
    }

    /**
     * Возвращает список сортируемых полей
     */
    protected static function sortableFields(): array
    {
        return [
            'point'   => ['field' => 'point', 'label' => __('users.assets')],
            'rating'  => ['field' => 'rating', 'label' => __('users.reputation')],
            'money'   => ['field' => 'money', 'label' => __('users.moneys')],
            'created' => ['field' => 'created_at', 'label' => __('main.registration_date')],
            'updated' => ['field' => 'updated_at', 'label' => __('users.last_visit')],
        ];
    }

    /**
     * Связь с таблицей online
     */
    public function online(): BelongsTo
    {
        return $this->belongsTo(Online::class, 'id', 'user_id')->withDefault();
    }

    /**
     * Возвращает последний бан
     */
    public function lastBan(): hasOne
    {
        return $this->hasOne(Banhist::class, 'user_id', 'id')
            ->whereIn('type', ['ban', 'change'])
            ->orderByDesc('created_at')
            ->withDefault();
    }

    /**
     * Возвращает заметку пользователя
     */
    public function note(): HasOne
    {
        return $this->hasOne(Note::class)->withDefault();
    }

    /**
     * Возвращает дополнительные поля
     */
    public function data(): HasMany
    {
        return $this->hasMany(UserData::class, 'user_id');
    }

    /**
     * Возвращает имя или логин пользователя
     */
    public function getName(): string
    {
        if ($this->id) {
            return $this->name ?: $this->login;
        }

        return setting('deleted_user');
    }

    /**
     * Возвращает ссылку на профиль пользователя
     */
    public function getProfile(): HtmlString
    {
        if ($this->id) {
            $admin = null;
            $name = check($this->getName());

            if ($this->color) {
                $name = '<span style="color:' . $this->color . '">' . $name . '</span>';
            }

            if (in_array($this->level, self::ADMIN_GROUPS, true)) {
                $admin = ' <i class="fas fa-xs fa-star text-info" title="' . $this->getLevel() . '"></i>';
            }

            $html = '<a class="section-author fw-bold" href="/users/' . $this->login . '" data-login="@' . $this->login . '">' . $name . '</a>';

            return new HtmlString($html . $admin);
        }

        $html = '<span class="section-author fw-bold" data-login="' . setting('deleted_user') . '">' . setting('deleted_user') . '</span>';

        return new HtmlString($html);
    }

    /**
     * Возвращает пол пользователя
     */
    public function getGender(): HtmlString
    {
        if ($this->gender === 'female') {
            return new HtmlString('<i class="fa fa-female fa-lg"></i>');
        }

        return new HtmlString('<i class="fa fa-male fa-lg"></i>');
    }

    /**
     * Авторизует пользователя
     */
    public static function auth(string $login, string $password, bool $remember = true): User|bool
    {
        if (! empty($login) && ! empty($password)) {
            $user = getUserByLoginOrEmail($login);

            if ($user && password_verify($password, $user->password)) {
                (new self())->rememberUser($user, $remember);

                // Сохранение привязки к соц. сетям
                if (app('session')->has('social')) {
                    Social::query()->create([
                        'user_id'    => $user->id,
                        'network'    => app('session')->get('social')->network,
                        'uid'        => app('session')->get('social')->uid,
                        'created_at' => SITETIME,
                    ]);
                }

                $user->saveVisit(Login::AUTH);

                return $user;
            }
        }

        return false;
    }

    /**
     * Авторизует через социальные сети
     *
     * @throws GuzzleException
     */
    public static function socialAuth(string $token): User|bool
    {
        $client = new Client(['timeout' => 30.0]);

        $response = $client->get('//ulogin.ru/token.php', [
            'query' => [
                'token' => $token,
                'host'  => $_SERVER['HTTP_HOST'],
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $network = json_decode($response->getBody()->getContents());

            session()->put('social', $network);

            $social = Social::query()
                ->where('network', $network->network)
                ->where('uid', $network->uid)
                ->first();

            if ($social && $user = getUserById($social->user_id)) {
                (new self())->rememberUser($user, true);

                $user->saveVisit(Login::SOCIAL);

                return $user;
            }
        }

        return false;
    }

    /**
     * Возвращает название уровня по ключу
     */
    public static function getLevelByKey(string $level): string
    {
        return match ($level) {
            self::BOSS   => __('main.boss'),
            self::ADMIN  => __('main.admin'),
            self::MODER  => __('main.moder'),
            self::EDITOR => __('main.editor'),
            self::USER   => __('main.user'),
            self::PENDED => __('main.pended'),
            self::BANNED => __('main.banned'),
            default      => setting('statusdef'),
        };
    }

    /**
     * Возвращает уровень пользователя
     */
    public function getLevel(): string
    {
        return self::getLevelByKey($this->level);
    }

    /**
     * Is user online
     */
    public function isOnline(): bool
    {
        static $visits;

        if (! $visits) {
            $visits = Cache::remember('visit', 10, static function () {
                return Online::query()
                    ->whereNotNull('user_id')
                    ->pluck('user_id', 'user_id')
                    ->all();
            });
        }

        return isset($visits[$this->id]);
    }

    /**
     * User online status
     */
    public function getOnline(): HtmlString
    {
        $online = '';

        if ($this->isOnline()) {
            $online = '<div class="user-status bg-success" title="' . __('main.online') . '"></div>';
        }

        return new HtmlString($online);
    }

    /**
     * Get last visit
     */
    public function getVisit(): string
    {
        if ($this->isOnline()) {
            $visit = __('main.online');
        } else {
            $visit = dateFixed($this->updated_at);
        }

        return $visit;
    }

    /**
     * Возвращает статус пользователя
     */
    public function getStatus(): HtmlString|string
    {
        static $status;

        if (! $this->id) {
            return setting('statusdef');
        }

        if (! $status) {
            $status = $this->getStatuses(6 * 3600);
        }

        if (isset($status[$this->id])) {
            return new HtmlString($status[$this->id]);
        }

        return setting('statusdef');
    }

    /**
     * Возвращает аватар пользователя
     */
    public function getAvatar(): HtmlString
    {
        if (! $this->id) {
            return new HtmlString($this->getAvatarGuest());
        }

        if ($this->avatar && file_exists(public_path($this->avatar))) {
            $avatar = $this->getAvatarImage();
        } else {
            // $avatar = $this->getGravatar();
            $avatar = $this->getAvatarDefault();
        }

        return new HtmlString('<a href="/users/' . $this->login . '">' . $avatar . '</a> ');
    }

    /**
     * Возвращает изображение аватара
     */
    public function getAvatarImage(): HtmlString
    {
        if (! $this->id) {
            return $this->getAvatarGuest();
        }

        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return new HtmlString('<img class="avatar-default rounded-circle" src="' . $this->avatar . '" alt="">');
        }

        return $this->getAvatarDefault();
    }

    /**
     * Get guest avatar
     */
    public function getAvatarGuest(): HtmlString
    {
        return new HtmlString('<img class="avatar-default rounded-circle" src="/assets/img/images/avatar_guest.png" alt=""> ');
    }

    /**
     * Возвращает аватар для пользователя по умолчанию
     */
    private function getAvatarDefault(): HtmlString
    {
        $name = $this->getName();
        $color = '#' . substr(dechex(crc32($this->login)), 0, 6);
        $letter = mb_strtoupper(Str::substr($name, 0, 1), 'utf-8');

        return new HtmlString('<span class="avatar-default rounded-circle" style="background:' . $color . '">' . $letter . '</span>');
    }

    /**
     * Get gravatar
     */
    private function getGravatar(): HtmlString
    {
        $hash = hash('sha256', $this->email);

        return new HtmlString('<img class="avatar-default rounded-circle" src="//gravatar.com/avatar/' . $hash . '?d=initials&amp;name=' . $this->getName() . '" alt="">');
    }

    /**
     * Кеширует статусы пользователей
     */
    public function getStatuses(int $seconds): array
    {
        return Cache::remember('status', $seconds, static function () {
            $users = self::query()
                ->select('users.id', 'users.status', 'status.name', 'status.color')
                ->leftJoin('status', static function (JoinClause $join) {
                    $join->whereRaw('users.point between status.topoint and status.point');
                })
                ->where('users.point', '>', 0)
                ->toBase()
                ->get();

            $statuses = [];
            foreach ($users as $user) {
                if ($user->status) {
                    $statuses[$user->id] = '<span style="color:#ff0000">' . check($user->status) . '</span>';
                    continue;
                }

                if ($user->color) {
                    $statuses[$user->id] = '<span style="color:' . $user->color . '">' . check($user->name) . '</span>';
                    continue;
                }

                $statuses[$user->id] = check($user->name);
            }

            return $statuses;
        });
    }

    /**
     * Находится ли пользователь в контактах
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
     * Находится ли пользователь в игноре
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
     */
    public function sendMessage(?User $author, string $text, bool $withAuthor = true): Builder|Model
    {
        return (new Message())->createDialogue($this, $author, $text, $withAuthor);
    }

    /**
     * Возвращает количество писем пользователя
     */
    public function getCountMessages(): int
    {
        return Dialogue::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает размер контакт-листа
     */
    public function getCountContact(): int
    {
        return Contact::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает размер игнор-листа
     */
    public function getCountIgnore(): int
    {
        return Ignore::query()->where('user_id', $this->id)->count();
    }

    /**
     * Возвращает количество записей на стене сообщений
     */
    public function getCountWall(): int
    {
        return Wall::query()->where('user_id', $this->id)->count();
    }

    /**
     * Удаляет альбом пользователя
     */
    public function deleteAlbum(): void
    {
        $photos = Photo::query()->where('user_id', $this->id)->get();

        if ($photos->isNotEmpty()) {
            foreach ($photos as $photo) {
                $photo->delete();
            }
        }
    }

    /**
     * Удаляет записи пользователя из всех таблиц
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            deleteFile(public_path($this->picture));
            deleteFile(public_path($this->avatar));

            Message::query()->where('user_id', $this->id)->delete();
            Dialogue::query()->where('user_id', $this->id)->delete();
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
        });
    }

    /**
     * Updates count messages
     */
    public function updatePrivate(): void
    {
        if ($this->newprivat) {
            $countDialogues = Dialogue::query()
                ->where('user_id', $this->id)
                ->where('reading', 0)
                ->count();

            if ($countDialogues !== $this->newprivat) {
                $this->update([
                    'newprivat'      => $countDialogues,
                    'sendprivatmail' => 0,
                ]);
            }
        }
    }

    /**
     * Check user banned
     */
    public function isBanned(): bool
    {
        return $this->level === self::BANNED;
    }

    /**
     * Check user pended
     */
    public function isPended(): bool
    {
        return setting('regkeys') && $this->level === self::PENDED;
    }

    /**
     * Check user active
     */
    public function isActive(): bool
    {
        return in_array($this->level, self::USER_GROUPS, true);
    }

    /**
     * Getting daily bonus
     */
    public function gettingBonus(): void
    {
        if ($this->isActive() && $this->timebonus < strtotime('-23 hours', SITETIME)) {
            $this->increment('money', setting('bonusmoney'));
            $this->update(['timebonus' => SITETIME]);

            setFlash('success', __('main.daily_bonus', ['money' => plural(setting('bonusmoney'), setting('moneyname'))]));
        }
    }

    /**
     * Сохраняет посещения
     */
    public function saveVisit(string $type): void
    {
        $authorization = Login::query()
            ->where('user_id', $this->id)
            ->where('created_at', '>', SITETIME - 60)
            ->first();

        if (! $authorization) {
            Login::query()->create([
                'user_id'    => $this->id,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'created_at' => SITETIME,
                'type'       => $type,
            ]);
        }

        $this->increment('visits');
    }

    /**
     * Remember user
     */
    private function rememberUser(User $user, bool $remember = false): void
    {
        if ($remember) {
            cookie()->queue(cookie()->forever('login', $user->login));
            cookie()->queue(cookie()->forever('password', $user->password));
        }

        app('session')->put('id', $user->id);
        app('session')->put('password', $user->password);
        app('session')->put('online');
    }
}
