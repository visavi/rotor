<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Http\Controllers\Controller;
use App\Models\Down;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopController extends Controller
{
    /**
     * Топ файлов
     */
    public function index(Request $request): View
    {
        $sort = $request->input('sort', 'rating');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Down::getSorting($sort, $order);

        $downs = Down::query()
            ->active()
            ->with('category', 'user')
            ->orderBy(...$orderBy)
            ->paginate(setting('downlist'))
            ->appends(compact('sort', 'order'));

        return view('loads/top', compact('downs', 'sorting'));
    }
}
