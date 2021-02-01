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
        $find  = $request->input('find');
        $downs = collect();

        if ($find) {
            $find = rawurldecode(trim(preg_replace('/[^\w\x7F-\xFF\s]/', ' ', $find)));

            $validator->length($find, 3, 64, ['find' => __('main.request_length')]);
            if ($validator->isValid()) {
                if (config('DB_DRIVER') === 'mysql') {
                    [$sql, $bindings] = ['MATCH (title, text) AGAINST (? IN BOOLEAN MODE)', [$find . '*']];
                } else {
                    [$sql, $bindings] = ['title ILIKE ? OR text ILIKE ?', ['%' . $find . '%', '%' . $find . '%']];
                }

                $downs = Down::query()
                    ->where('active', 1)
                    ->whereRaw($sql, $bindings)
                    ->with('user', 'category')
                    ->paginate(setting('downlist'))
                    ->appends([
                        'find' => $find,
                    ]);

                if ($downs->isEmpty()) {
                    setInput($request->all());
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
