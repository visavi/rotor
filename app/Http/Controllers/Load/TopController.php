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
        $sort = check($request->input('sort'));
        $order = match ($sort) {
            'rating'   => 'rating',
            'comments' => 'count_comments',
            default    => 'loads',
        };

        $downs = Down::query()
            ->active()
            ->orderByDesc($order)
            ->with('category', 'user')
            ->paginate(setting('downlist'))
            ->appends(['sort' => $sort]);

        return view('loads/top', compact('downs', 'order'));
    }
}
