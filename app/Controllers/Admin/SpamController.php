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
use Exception;
use Illuminate\Http\Request;

class SpamController extends AdminController
{
    /**
     * @var array
     */
    private $types;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $total = [];

    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort(403, __('errors.forbidden'));
        }

        $this->types = [
            'posts'     => __('index.forums'),
            'guestbook' => __('index.guestbook'),
            'messages'  => __('index.messages'),
            'walls'     => __('index.wall_posts'),
            'comments'  => __('main.comments'),
        ];

        $type = check(request()->input('type'));
        $this->type = isset($this->types[$type]) ? $type : 'post';

        $spam = Spam::query()
            ->selectRaw('relate_type, count(*) as total')
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        foreach ($this->types as $key => $value) {
            $this->total[$key] = $spam[$key] ?? 0;
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $type  = $this->type;
        $types = $this->types;

        /** @var Spam $records */
        $records = Spam::query()
            ->where('relate_type', $type)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('spamlist'))
            ->appends(['type' => $type]);

        if (in_array($type, ['message', 'wall'])) {
            $records->load('relate.author');
        } else {
            $records->load('relate.user');
        }

        $total = $this->total;

        return view('admin/spam/index', compact('records', 'total', 'type', 'types'));
    }

    /**
     * Удаление жалоб
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws Exception
     */
    public function delete(Request $request, Validator $validator): void
    {
        $id    = int($request->input('id'));
        $token = check($request->input('token'));

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->notEmpty($id, __('validator.deletion'));

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
