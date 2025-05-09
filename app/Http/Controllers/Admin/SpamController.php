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
    private array $types;
    private string $type;
    private array $total = [];

    /**
     * SpamController constructor.
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

        $spam = Spam::query()
            ->selectRaw('relate_type, count(*) as total')
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        if ($type) {
            $this->type = isset($this->types[$type]) ? $type : 'post';
        } elseif ($spam) {
            $this->type = array_search(max($spam), $spam, true);
        } else {
            $this->type = 'post';
        }

        foreach ($this->types as $key => $value) {
            $this->total[$key] = $spam[$key] ?? 0;
        }
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $type = $this->type;
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
