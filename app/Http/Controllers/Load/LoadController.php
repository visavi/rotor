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
     */
    public function index(): View
    {
        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new', 'lastDown.user')
            ->orderBy('sort')
            ->get();

        if ($categories->isEmpty()) {
            abort(200, __('loads.empty_loads'));
        }

        return view('loads/index', compact('categories'));
    }

    /**
     * Список файлов в категории
     */
    public function load(int $id, Request $request): View
    {
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.load_not_exist'));
        }

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Down::getSorting($sort, $order);

        $downs = Down::query()
            ->active()
            ->where('category_id', $category->id)
            ->orderBy(...$orderBy)
            ->with('user')
            ->paginate(setting('downlist'))
            ->appends(compact('sort', 'order'));

        return view('loads/load', compact('category', 'downs', 'sorting'));
    }

    /**
     * RSS всех файлов
     */
    public function rss(): View
    {
        $downs = Down::query()
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        if ($downs->isEmpty()) {
            abort(200, __('loads.downs_not_found'));
        }

        return view('loads/rss', compact('downs'));
    }
}
