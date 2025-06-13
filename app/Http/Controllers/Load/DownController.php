<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Down;
use App\Models\File;
use App\Models\Flood;
use App\Models\Load;
use App\Models\Reader;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class DownController extends Controller
{
    /**
     * Просмотр загрузки
     */
    public function view(int $id): View
    {
        $down = Down::query()
            ->select('downs.*', 'polls.vote')
            ->where('downs.id', $id)
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('downs.id', 'polls.relate_id')
                    ->where('polls.relate_type', Down::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! isAdmin(User::ADMIN) && (! $down->active && getUser() && getUser('id') !== $down->user_id)) {
            abort(200, __('loads.down_not_verified'));
        }

        $allowDownload = getUser() || setting('down_guest_download');

        return view('loads/down', compact('down', 'allowDownload'));
    }

    /**
     * Создание загрузки
     */
    public function create(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $cid = int($request->input('category'));

        if (! isAdmin() && ! setting('downupload')) {
            abort(200, __('loads.down_closed'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $categories = (new Load())->getChildren();

        if ($categories->isEmpty()) {
            abort(200, __('loads.empty_loads'));
        }

        $files = File::query()
            ->where('relate_type', Down::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $user->id);

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $links = (array) $request->input('links');
            $links = array_unique(array_diff($links, ['']));

            $category = Load::query()->find($cid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, setting('down_title_min'), setting('down_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('down_text_min'), setting('down_text_max'), ['text' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['category' => __('loads.load_closed')]);

                $duplicate = Down::query()->where('title', $title)->count();
                $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);
            }

            $validator->notEmpty($files->count() + count($links), ['files' => __('validator.file_upload_one')]);
            $validator->lte($files->count() + count($links), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                foreach ($links as $link) {
                    $validator->length($link, 5, 100, ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);
                }
            }

            if ($validator->isValid()) {
                $down = Down::query()->create([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                    'active'      => isAdmin(User::ADMIN),
                    'links'       => $links ? array_values($links) : null,
                ]);

                $files->update(['relate_id' => $down->id]);

                if (isAdmin(User::ADMIN)) {
                    $down->category->increment('count_downs');
                    clearCache(['statLoads', 'recentDowns', 'DownFeed']);
                } else {
                    $admins = User::query()->whereIn('level', [User::BOSS, User::ADMIN])->get();

                    if ($admins->isNotEmpty()) {
                        $text = textNotice('down_upload', ['url' => route('admin.downs.edit', ['id' => $down->id], false), 'title' => $down->title]);

                        foreach ($admins as $admin) {
                            $admin->sendMessage($user, $text, false);
                        }
                    }
                }

                $flood->saveState();
                setFlash('success', __('loads.down_added_success'));

                return redirect()->route('downs.view', ['id' => $down->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $down = new Down();
        $files = $files->get();

        return view('loads/create', compact('categories', 'down', 'cid', 'files'));
    }

    /**
     * Редактирование загрузки
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $cid = int($request->input('category'));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $down = Down::query()->where('user_id', $user->id)->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($down->active) {
            abort(200, __('loads.down_verified'));
        }

        $files = File::query()
            ->where('relate_type', Down::$morphName)
            ->where('relate_id', $down->id)
            ->where('user_id', $user->id)
            ->get();

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $links = (array) $request->input('links');
            $links = array_unique(array_diff($links, ['']));

            $category = Load::query()->find($cid);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, setting('down_title_min'), setting('down_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('down_text_min'), setting('down_text_max'), ['text' => __('validator.text')])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')])
                ->empty($category->closed, ['category' => __('loads.load_closed')]);

            $duplicate = Down::query()
                ->where('title', $title)
                ->where('id', '<>', $down->id)
                ->count();

            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);
            $validator->notEmpty($files->count() + count($links), ['files' => __('validator.file_upload_one')]);
            $validator->lte($files->count() + count($links), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                foreach ($links as $link) {
                    $validator->length($link, setting('down_link_min'), setting('down_link_max'), ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);
                }
            }

            if ($validator->isValid()) {
                $links = setting('down_allow_links') ? array_values($links) : null;

                $down->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'links'       => $links,
                ]);

                clearCache(['statLoads', 'recentDowns', 'DownFeed']);
                setFlash('success', __('loads.down_edited_success'));

                return redirect()->route('downs.view', ['id' => $down->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = $down->category->getChildren();

        return view('loads/edit', compact('categories', 'down', 'cid', 'files'));
    }

    /**
     * Скачивание файла
     */
    public function download(int $id, int $fid, Validator $validator): Response
    {
        $file = File::query()->where('relate_type', Down::$morphName)->find($fid);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! (getUser() || setting('down_guest_download'))) {
            abort(403, __('loads.download_authorized'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        $validator->true(file_exists(public_path($file->path)), __('loads.down_not_exist'));

        if ($validator->isValid()) {
            Reader::countingStat($file->relate);

            return response()->download(public_path($file->path), $file->name);
        }

        setFlash('danger', $validator->getErrors());

        return redirect()->route('downs.view', ['id' => $file->relate->id]);
    }

    /**
     * Скачивание файла по ссылке
     */
    public function downloadLink(int $id, int $linkId, Validator $validator): Response
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! (getUser() || setting('down_guest_download'))) {
            abort(403, __('loads.download_authorized'));
        }

        if (! $down->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        $validator->true($down->links[$linkId] ?? false, __('loads.down_not_exist'));

        if ($validator->isValid()) {
            Reader::countingStat($down);

            return response()->redirectTo($down->links[$linkId]);
        }

        setFlash('danger', $validator->getErrors());

        return redirect()->route('downs.view', ['id' => $down->id]);
    }

    /**
     * Комментарии
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $down->active) {
            abort(200, __('loads.down_not_verified'));
        }

        $cid = int($request->input('cid'));
        if ($cid) {
            $total = $down->comments->where('id', '<=', $cid)->count();

            $page = ceil($total / setting('comments_per_page'));
            $page = $page > 1 ? $page : null;

            return redirect()->route('downs.comments', ['id' => $down->id, 'page' => $page])
                ->withFragment('comment_' . $cid);
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            if ($validator->isValid()) {
                $comment = $down->comments()->create([
                    'text'       => antimat($msg),
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $down->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, route('downs.comments', ['id' => $down->id, 'cid' => $comment->id], false), $down->title);

                setFlash('success', __('main.comment_added_success'));

                $page = ceil($down->count_comments / setting('comments_per_page'));
                $page = $page > 1 ? $page : null;

                return redirect()->route('downs.comments', ['id' => $down->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $down->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('loads/comments', compact('down', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $page = int($request->input('page', 1));

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = $down->comments()
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect()->route('downs.comments', ['id' => $id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('loads/editcomment', compact('down', 'comment', 'page'));
    }

    /**
     * Редирект на новый роут (Будет удален в будущем)
     */
    public function redirectZip(int $id, ?int $fid = null): RedirectResponse
    {
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);
        $down = $file?->relate;

        if (! $file || ! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($fid) {
            return redirect()->to("/downs/{$down->id}/zip/{$id}/{$fid}", 301);
        }

        return redirect()->to("/downs/{$down->id}/zip/{$id}", 301);
    }

    /**
     * Просмотр zip архива
     */
    public function zip(int $id, int $fid): View
    {
        $down = Down::query()->find($id);
        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $down->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        $file = $down->files->firstWhere('id', $fid);
        if (! $file) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($file->extension !== 'zip') {
            abort(200, __('loads.archive_only_zip'));
        }

        $archive = new ZipArchive();
        $opened = $archive->open(public_path($file->path), ZipArchive::RDONLY);

        if ($opened !== true) {
            abort(200, __('loads.archive_not_open'));
        }

        $documents = [];
        for ($i = 0; $i < $archive->count(); $i++) {
            $stat = $archive->statIndex($i);

            $documents[] = [
                'index' => $stat['index'],
                'name'  => $stat['name'],
                'size'  => $stat['size'],
                'isDir' => str_ends_with($stat['name'], '/'),
                'ext'   => getExtension($stat['name']),
            ];
        }

        $archive->close();

        /* uasort($documents, static function ($a, $b) {
            if ($a['isDir'] && ! $b['isDir']) {
                return -1;
            }
            if (! $a['isDir'] && $b['isDir']) {
                return 1;
            }

            return strcmp($a['name'], $b['name']);
        });*/

        /*$tree = [];
        foreach ($documents as $key => $document) {
            $path = $archive->getNameIndex($key);
            $pathBySlash = array_filter(explode('/', $path));
            $count = count($pathBySlash) - 1;
            $temp = &$tree;

            for ($j = 0; $j < $count; $j++) {
                if (! isset($temp[$pathBySlash[$j]])) {
                    $temp[$pathBySlash[$j]] = [];
                }

                $temp = &$temp[$pathBySlash[$j]];
            }

            if (str_ends_with($path, '/')) {
                $temp[$pathBySlash[$count]] = [];
            } else {
                $temp[] = $pathBySlash[$count];
            }
        }*/

        $documents = paginate($documents, setting('ziplist'));

        return view('loads/zip', compact('down', 'file', 'documents'));
    }

    /**
     * Просмотр файла в zip архиве
     */
    public function zipView(int $id, int $fid, int $zid): View
    {
        $down = Down::query()->find($id);
        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $down->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        $file = $down->files->firstWhere('id', $fid);
        if (! $file) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($file->extension !== 'zip') {
            abort(200, __('loads.archive_only_zip'));
        }

        $archive = new ZipArchive();
        $opened = $archive->open(public_path($file->path), ZipArchive::RDONLY);

        if ($opened !== true) {
            abort(200, __('loads.archive_not_open'));
        }
        $content = $archive->getFromIndex($zid);
        $document = $archive->statIndex($zid);

        if ($content === false) {
            abort(200, __('loads.file_not_read'));
        }

        $archive->close();

        $ext = getExtension($document['name']);

        if (! in_array($ext, $down->getViewExt(), true)) {
            abort(200, __('loads.file_not_read'));
        }

        if (
            $document['size'] > 0
            && preg_match("/\.(gif|png|bmp|jpg|jpeg|webp)$/", $document['name'])
        ) {
            header('Content-type: image/' . $ext);
            header('Content-Length: ' . strlen($content));
            header('Content-Disposition: inline; filename="' . $document['name'] . '";');
            exit($content);
        }

        if (! isUtf($content)) {
            $content = winToUtf($content);
        }

        return view('loads/zip_view', compact('down', 'file', 'document', 'content'));
    }

    /**
     * RSS комментариев
     */
    public function rss(int $id): View
    {
        $down = Down::query()->where('id', $id)->with('lastComments')->first();

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        return view('loads/rss_comments', compact('down'));
    }
}
