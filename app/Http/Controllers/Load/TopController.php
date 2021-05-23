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
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $sort = check($request->input('sort'));

        switch ($sort) {
            case 'rated':
                $order = 'rated';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            default:
                $order = 'loads';
        }

        $downs = Down::query()
            ->where('active', 1)
            ->orderByDesc($order)
            ->with('category', 'user')
            ->paginate(setting('downlist'))
            ->appends(['sort' => $sort]);

        return view('loads/top', compact('downs', 'order'));
    }
}
