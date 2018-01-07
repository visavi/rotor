<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Down;
use App\Models\Guest;
use App\Models\Inbox;
use App\Models\News;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Spam;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;

class SpamController extends AdminController
{
    /**
     * @var array
     */
    private $types;

    /**
     * @var array
     */
    private $total = [];

    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
        }

        $this->types = [
            'post'  => Post::class,
            'guest' => Guest::class,
            'photo' => Photo::class,
            'blog'  => Blog::class,
            'inbox' => Inbox::class,
            'wall'  => Wall::class,
            'news'  => News::class,
            'down'  => Down::class,
        ];

        $spam = Spam::query()
            ->select('relate_type', DB::raw('count(*) as total'))
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        foreach ($this->types as $key => $value) {
            $this->total[$key] = $spam[$value] ?? 0;
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $type = check(Request::input('type'));
        $type = isset($this->types[$type]) ? $type : 'post';

        $page = paginate(setting('spamlist'),  $this->total[$type]);

        $records = Spam::query()
            ->where('relate_type', $this->types[$type])
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('spamlist'))
            ->with('relate.user', 'user')
            ->get();

        $total = $this->total;

        return view('admin/spam/index', compact('records', 'page', 'total', 'type'));
    }

    /**
     * Удаление жалоб
     */
    public function delete()
    {
        $id    = int(Request::input('id'));
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->true(Request::ajax(), 'Это не ajax запрос!')
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($id, 'Не выбрана запись для удаление!');

        if ($validator->isValid()) {

            Spam::query()->find($id)->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
