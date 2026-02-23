<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SearchController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $count = Search::query()->count();

        $search = Search::query()
            ->selectRaw('relate_type, count(*) as cnt')
            ->groupBy('relate_type')
            ->get();

        return view('admin/search/index', compact('count', 'search'));
    }

    /**
     * Импорт
     */
    public function import(): RedirectResponse
    {
        Artisan::call('search:import');

        setFlash('success', __('main.records_added_success'));

        return redirect()->route('admin.search.index');
    }
}
