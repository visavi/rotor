<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\Down;
use App\Models\Load;

class SearchController extends BaseController
{
    /**
     * Поиск
     *
     * @return string
     */
    public function index(): ?string
    {
        $cid     = check(Request::input('cid'));
        $find    = check(Request::input('find'));
        $type    = int(Request::input('type'));
        $where   = int(Request::input('where'));
        $section = int(Request::input('section'));

        if (! $find) {
            $categories = Load::query()
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('sort')
                ->get();

            if ($categories->isEmpty()) {
                abort('default', 'Разделы загрузок еще не созданы!');
            }

            return view('loads/search', compact('categories', 'cid'));
        }

        $find = str_replace(['@', '+', '-', '*', '~', '<', '>', '(', ')', '"', "'"], '', $find);

        if (! isUtf($find)) {
            $find = winToUtf($find);
        }

        if (utfStrlen($find) >= 3 && utfStrlen($find) <= 50) {

            $findmewords = explode(' ', utfLower($find));

            $arrfind = [];
            foreach ($findmewords as $val) {
                if (utfStrlen($val) >= 3) {
                    $arrfind[] = empty($type) ? '+' . $val . '*' : $val . '*';
                }
            }

            $findme = implode(' ', $arrfind);

            if ($type === 2 && \count($findmewords) > 1) {
                $findme = "\"$find\"";
            }

            $wheres = empty($where) ? 'title' : 'text';

            $loadfind = ($type . $wheres . $section . $find);

            // Поиск в названии
            if ($wheres === 'title') {

                if (empty($_SESSION['loadfindres']) || $loadfind !== $_SESSION['loadfind']) {

                    $searchsec = ($section > 0) ? 'category_id = ' . $section . ' AND' : '';

                    $result = Down::query()
                        ->select('id')
                        ->where('active', 1)
                        ->whereRaw($searchsec . ' MATCH (`title`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                        ->limit(100)
                        ->pluck('id')
                        ->all();

                    $_SESSION['loadfind'] = $loadfind;
                    $_SESSION['loadfindres'] = $result;
                }

                $total = \count($_SESSION['loadfindres']);

                if ($total > 0) {
                    $page = paginate(setting('downlist'), $total);

                    $downs = Down::query()
                        ->whereIn('id', $_SESSION['loadfindres'])
                        ->orderBy('created_at', 'desc')
                        ->offset($page->offset)
                        ->limit($page->limit)
                        ->with('user', 'category')
                        ->get();

                    return view('loads/search_title', compact('downs', 'page', 'find', 'type', 'where', 'section'));
                }

                setInput(Request::all());
                setFlash('danger', 'По вашему запросу ничего не найдено!');
                redirect('/loads/search');
            }

            // Поиск в описании
            if ($wheres === 'text') {

                if (empty($_SESSION['loadfindres']) || $loadfind !== $_SESSION['loadfind']) {

                    $searchsec = ($section > 0) ? 'category_id = ' . $section . ' AND' : '';

                    $result = Down::query()
                        ->select('id')
                        ->where('active', 1)
                        ->whereRaw($searchsec . ' MATCH (`text`) AGAINST (? IN BOOLEAN MODE)', [$findme])
                        ->limit(100)
                        ->pluck('id')
                        ->all();

                    $_SESSION['loadfind'] = $loadfind;
                    $_SESSION['loadfindres'] = $result;
                }

                $total = \count($_SESSION['loadfindres']);

                if ($total > 0) {
                    $page = paginate(setting('downlist'), $total);

                    $downs = Down::query()
                        ->whereIn('id', $_SESSION['loadfindres'])
                        ->orderBy('created_at', 'desc')
                        ->offset($page->offset)
                        ->limit($page->limit)
                        ->with('user', 'category')
                        ->get();

                    return view('loads/search_text', compact('downs', 'page', 'find', 'type', 'where', 'section'));
                }

                setInput(Request::all());
                setFlash('danger', 'По вашему запросу ничего не найдено!');
                redirect('/loads/search');
            }

        } else {
            setInput(Request::all());
            setFlash('danger', ['find' => 'Запрос должен содержать от 3 до 50 символов!']);
            redirect('/loads/search');
        }
    }
}

