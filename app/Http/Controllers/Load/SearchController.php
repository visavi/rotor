<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Down;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Поиск
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        $find  = $request->input('find');
        $downs = collect();

        if ($find) {
            $find = rawurldecode(trim(preg_replace('/[^\w\x7F-\xFF\s]/', ' ', $find)));

            $validator->length($find, 3, 64, ['find' => __('main.request_length')]);
            if ($validator->isValid()) {
                if (config('database.default') === 'mysql') {
                    [$sql, $bindings] = ['MATCH (title, text) AGAINST (? IN BOOLEAN MODE)', [$find . '*']];
                } else {
                    [$sql, $bindings] = ['title ILIKE ? OR text ILIKE ?', ['%' . $find . '%', '%' . $find . '%']];
                }

                $downs = Down::query()
                    ->where('active', 1)
                    ->whereRaw($sql, $bindings)
                    ->with('user', 'category')
                    ->paginate(setting('downlist'))
                    ->appends(compact('find'));

                if ($downs->isEmpty()) {
                    setInput($request->all());
                    setFlash('danger', __('main.empty_found'));

                    return redirect('loads/search');
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/search', compact('downs', 'find'));
    }
}
