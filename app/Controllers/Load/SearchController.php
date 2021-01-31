<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Down;
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
        $find = rawurldecode($request->input('find', ''));
        $find = trim(preg_replace("/[^\w\x7F-\xFF\s]/", ' ', $find));
        $downs = collect();

        if ($find) {
            $validator->length($find, 3, 64, ['find' => __('main.request_requirements')]);

            if ($validator->isValid()) {
                $downs = Down::query()
                    ->where('active', 1)
                    ->whereRaw('MATCH (title, text) AGAINST (? IN BOOLEAN MODE)', [$find . '*'])
                    ->with('user', 'category')
                    ->limit(500)
                    ->paginate(setting('downlist'))
                    ->appends([
                        'find' => $find,
                    ]);

                if ($downs->isEmpty()) {
                    setFlash('danger', __('main.empty_found'));
                    redirect('/loads/search');
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/search', compact('downs', 'find'));
    }
}
