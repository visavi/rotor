<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\Status;
use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Главная страница
     */
    public function index(string $page = 'index'): View
    {
        if (
            ! preg_match('|^[a-z0-9_\-]+$|i', $page)
            || ! file_exists(resource_path('views/main/' . $page . '.blade.php'))
        ) {
            abort(404);
        }

        return view('main/layout', compact('page'));
    }

    /**
     * Правила
     */
    public function rules(): View
    {
        $rules = Rule::query()->first();

        if ($rules) {
            $rules['text'] = str_replace('%SITENAME%', setting('title'), $rules['text']);
        }

        return view('pages/rules', compact('rules'));
    }

    /**
     * Стикеры
     */
    public function stickers(): View
    {
        $categories = StickersCategory::query()
            ->selectRaw('sc.id, sc.name, count(s.id) cnt')
            ->from('stickers_categories as sc')
            ->leftJoin('stickers as s', 's.category_id', 'sc.id')
            ->groupBy('sc.id')
            ->orderBy('sc.id')
            ->get();

        return view('pages/stickers', compact('categories'));
    }

    /**
     * Стикеры
     */
    public function stickersCategory(int $id): View
    {
        $category = StickersCategory::query()->where('id', $id)->first();

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        $stickers = Sticker::query()
            ->where('category_id', $id)
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->with('category')
            ->paginate(setting('stickerlist'));

        return view('pages/stickers_category', compact('category', 'stickers'));
    }

    /**
     * FAQ по сайту
     */
    public function faq(): View
    {
        return view('pages/faq');
    }

    /**
     * FAQ по статусам
     */
    public function statusfaq(): View
    {
        $statuses = Status::query()
            ->orderByDesc('topoint')
            ->get();

        return view('pages/statusfaq', compact('statuses'));
    }
}
