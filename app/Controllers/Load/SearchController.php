<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Down;
use App\Models\Load;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    /**
     * Поиск
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        $find = check($request->input('find'));
        $downs = collect();

        if ($find) {
            $validator->length($find, 4, 64, ['find' => __('main.request_requirements')]);

            if ($validator->isValid()) {
                $downs = Down::query()
                    ->selectRaw('downs.*, MATCH (title, text) AGAINST (? IN BOOLEAN MODE) as score', [$find])
                    ->where('active', 1)
                    ->whereRaw('MATCH (title, text) AGAINST (? IN BOOLEAN MODE)', [$find])
                    ->with('user', 'category')
                    ->orderByDesc('score')
                    ->orderByDesc('created_at')
                    ->paginate(setting('downlist'))
                    ->appends([
                        'find' => $find,
                    ]);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/search', compact('downs', 'find'));
    }
}
