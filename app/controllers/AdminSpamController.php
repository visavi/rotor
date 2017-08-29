<?php

class AdminSpamController extends BaseAdminController
{
    /**
     * @var array
     */
    public $types;

    /**
     * @var array
     */
    public $total;

    public function __construct()
    {
        if (! is_admin([101, 102, 103])) {
            App::abort('403', 'Доступ запрещен!');
        }

        $this->types = [
            'post'  => Post::class,
            'guest' => Guest::class,
            'photo' => Photo::class,
            'blog'  => Blog::class,
            'inbox' => Inbox::class,
            'wall'  => Wall::class,
        ];

        $this->total = Spam::select(Capsule::raw("
            SUM(relate_type='".Post::class."') post,
            SUM(relate_type='".Guest::class."') guest,
            SUM(relate_type='".Photo::class."') photo,
            SUM(relate_type='".Blog::class."') blog,
            SUM(relate_type='".Inbox::class."') inbox,
            SUM(relate_type='".Wall::class."') wall
        "))->first();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $type = check(Request::input('type'));
        $type = isset($this->types[$type]) ? $type : 'post';

        $page = App::paginate(Setting::get('spamlist'),  $this->total['post']);

        $records = Spam::where('relate_type', $this->types[$type])
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(Setting::get('spamlist'))
            ->with('relate.user', 'user')
            ->get();

        $total = $this->total;

        App::view('admin/spam/index', compact('records', 'page', 'total', 'type'));
    }

    /**
     * Удаление жалоб
     */
    public function delete()
    {
        $id    = abs(intval(Request::input('id')));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation
            ->addRule('bool', Request::ajax(), 'Это не ajax запрос!')
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('not_empty', $id, ['Не выбрана запись для удаление!']);

        if ($validation->run()) {

            Spam::find($id)->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validation->getErrors())
            ]);
        }
    }
}
