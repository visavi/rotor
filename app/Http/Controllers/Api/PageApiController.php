<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use App\Models\Status;
use App\Models\Sticker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Статичные страницы сайта
 */
class PageApiController extends Controller
{
    /**
     * Страница из resources/views/main
     *
     * Содержимое верстают на сайте, поэтому приходит готовым HTML
     */
    public function page(string $page = 'index'): JsonResponse
    {
        if (
            ! preg_match('|^[a-z0-9_\-]+$|i', $page)
            || ! file_exists(resource_path('views/main/' . $page . '.blade.php'))
        ) {
            abort(404, __('main.record_not_found'));
        }

        return response()->json([
            'slug' => $page,
            'html' => trim(view('main/' . $page)->render()),
        ]);
    }

    /**
     * Правила сайта
     */
    public function rules(): JsonResponse
    {
        $rules = Rule::query()->first();

        return response()->json([
            'text' => $rules ? str_replace('%SITENAME%', setting('title'), $rules->text) : null,
        ]);
    }

    /**
     * Стикеры по категориям
     *
     * Нужны редактору сообщений: код подставляется в текст, картинка показывается в списке
     */
    public function stickers(): JsonResponse
    {
        $stickers = Sticker::query()
            ->with('category:id,name')
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->get();

        $categories = $stickers
            ->groupBy('category_id')
            ->toBase()
            ->map(static fn ($items, $categoryId) => [
                'id'       => (int) $categoryId,
                'name'     => $items->first()->category->name,
                'stickers' => $items
                    ->map(static fn (Sticker $sticker) => [
                        'code' => $sticker->code,
                        'url'  => url($sticker->name),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values();

        return response()->json(['data' => $categories]);
    }

    /**
     * Статусы пользователей и пороги баллов
     */
    public function statuses(): JsonResponse
    {
        $statuses = Status::query()
            ->orderByDesc('topoint')
            ->get()
            ->map(static fn (Status $status) => [
                'name'    => $status->name,
                'color'   => $status->color,
                'point'   => $status->point,
                'topoint' => $status->topoint,
            ]);

        return response()->json(['data' => $statuses]);
    }
}
