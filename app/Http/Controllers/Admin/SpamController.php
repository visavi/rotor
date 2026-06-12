<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Registry;
use App\Classes\Validator;
use App\Models\Spam;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        $moduleTypes = [];
        foreach (Registry::$spamTypes as $morphName) {
            $moduleTypes[$morphName] = Registry::$labelTypes[$morphName] ?? $morphName;
        }

        $this->types = array_merge([
            'messages' => __('index.private_messages'),
            'comments' => __('main.comments'),
        ], $moduleTypes);

        $type = $request->input('type');

        $spam = Spam::query()
            ->selectRaw('relate_type, count(*) as total')
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        if ($type && isset($this->types[$type])) {
            $this->type = $type;
        } elseif ($spam) {
            $this->type = array_search(max($spam), $spam, true);
        } else {
            $this->type = array_key_first($this->types);
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

        // У Message и Wall автор текста в связи author, у остальных моделей — user
        $model = Relation::getMorphedModel($type);
        $relateWith = $model && method_exists($model, 'author')
            ? 'relate.author'
            : 'relate.user';

        $records = Spam::query()
            ->where('relate_type', $type)
            ->orderByDesc('created_at')
            ->with(['user', $relateWith])
            ->paginate(setting('spamlist'))
            ->appends(['type' => $type]);

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
