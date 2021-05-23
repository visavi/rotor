<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Http\Controllers\Controller;
use App\Models\Down;
use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoadController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        if ($categories->isEmpty()) {
            abort('default', __('loads.empty_loads'));
        }

        return view('loads/index', compact('categories'));
    }

    /**
     * Список файлов в категории
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function load(int $id, Request $request): View
    {
        /** @var Load $category */
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.load_not_exist'));
        }

        $sort = check($request->input('sort', 'time'));

        switch ($sort) {
            case 'rated':
                $order = 'rated';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            case 'loads':
                $order = 'loads';
                break;
            default:
                $order = 'created_at';
        }

        $downs = Down::query()
            ->where('category_id', $category->id)
            ->where('active', 1)
            ->orderByDesc($order)
            ->paginate(setting('downlist'))
            ->appends(['sort' => $sort]);

        return view('loads/load', compact('category', 'downs', 'order'));
    }

    /**
     * RSS всех файлов
     *
     * @return View
     */
    public function rss(): View
    {
        $downs = Down::query()
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        if ($downs->isEmpty()) {
            abort('default', __('loads.downs_not_found'));
        }

        return view('loads/rss', compact('downs'));
    }
}
