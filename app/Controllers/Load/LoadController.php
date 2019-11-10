<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Controllers\BaseController;
use App\Models\Down;
use App\Models\Load;
use Illuminate\Http\Request;

class LoadController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
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
     * @return string
     */
    public function load(int $id, Request $request): string
    {
        /** @var Load $category */
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.category_not_exist'));
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
     * @return string
     */
    public function rss(): string
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
