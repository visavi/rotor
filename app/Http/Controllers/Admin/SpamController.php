<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Spam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    /**
     * SpamController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->types = [
            'posts'     => __('index.forums'),
            'guestbook' => __('index.guestbook'),
            'messages'  => __('index.messages'),
            'walls'     => __('index.wall_posts'),
            'comments'  => __('main.comments'),
        ];

        $type = $request->input('type');
        $this->type = isset($this->types[$type]) ? $type : 'posts';

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
     * @return View
     */
    public function index(): View
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
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Validator $validator): JsonResponse
    {
        $id = int($request->input('id'));

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($id, __('validator.deletion'));

        if ($validator->isValid()) {
            $spam = Spam::query()->find($id);

            if ($spam) {
                $spam->delete();
            }

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }
}
