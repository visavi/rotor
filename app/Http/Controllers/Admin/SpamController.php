<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Spam;
use App\Support\Registry;
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
            $moduleTypes[$morphName] = Registry::label($morphName) ?? $morphName;
        }

        $this->types = array_merge([
            'messages' => __('index.private_messages'),
            'comments' => __('main.comments'),
        ], $moduleTypes);

        $type = $request->input('type');

        // Жалобы на записи отключённых модулей не выводятся: их тип не в списке
        $spam = Spam::query()
            ->whereIn('relate_type', array_keys($this->types))
            ->selectRaw('relate_type, count(*) as total')
            ->groupBy('relate_type')
            ->pluck('total', 'relate_type')
            ->all();

        if (is_string($type) && isset($this->types[$type])) {
            $this->type = $type;
        } elseif ($spam) {
            $this->type = (string) array_search(max($spam), $spam, true);
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
    public function delete(Request $request): JsonResponse
    {
        if (! $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => __('validator.not_ajax'),
            ], 400);
        }

        $id = $request->integer('id');

        if (! $id) {
            return response()->json([
                'success' => false,
                'message' => __('validator.deletion'),
            ], 422);
        }

        Spam::query()->find($id)?->delete();

        return response()->json([
            'success' => true,
            'message' => __('main.record_deleted_success'),
        ]);
    }
}
