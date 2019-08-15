<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Guestbook;
use App\Models\Message;
use App\Models\Post;
use App\Models\Spam;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Http\Request;

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
            abort(403, trans('errors.forbidden'));
        }

        $this->types = [
            'post'    => Post::class,
            'guest'   => Guestbook::class,
            'message' => Message::class,
            'wall'    => Wall::class,
            'comment' => Comment::class,
        ];

        $spam = Spam::query()
            ->selectRaw('relate_type, count(*) as total')
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        foreach ($this->types as $key => $value) {
            $this->total[$key] = $spam[$value] ?? 0;
        }
    }

    /**
     * Главная страница
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $type = check($request->input('type'));
        $type = isset($this->types[$type]) ? $type : 'post';

        $page = paginate(setting('spamlist'),  $this->total[$type]);

        $records = Spam::query()
            ->where('relate_type', $this->types[$type])
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('relate.user', 'user')
            ->get();

        if (in_array($type, ['message', 'wall'])) {
            $records->load('relate.author');
        }

        $total = $this->total;

        return view('admin/spam/index', compact('records', 'page', 'total', 'type'));
    }

    /**
     * Удаление жалоб
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(Request $request, Validator $validator): void
    {
        $id    = int($request->input('id'));
        $token = check($request->input('token'));

        $validator
            ->true($request->ajax(), 'Это не ajax запрос!')
            ->equal($token, $_SESSION['token'], trans('validator.token'))
            ->notEmpty($id, 'Не выбрана запись для удаление!');

        if ($validator->isValid()) {

            $spam = Spam::query()->find($id);

            if ($spam) {
                $spam->delete();
            }

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
