<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Load;
use App\Models\User;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Flood;
use App\Models\Reader;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use Symfony\Component\HttpFoundation\Response;

class DownController extends Controller
{
    /**
     * Просмотр загрузки
     *
     * @param int $id
     *
     * @return View
     */
    public function index(int $id): View
    {
        $down = Down::query()
            ->select('downs.*', 'pollings.vote')
            ->where('downs.id', $id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('downs.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Down::$morphName)
                    ->where('pollings.user_id', getUser('id'));
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
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return View|RedirectResponse
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

        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($categories->isEmpty()) {
            abort(200, __('loads.empty_loads'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $files = (array) $request->file('files');
            $links = (array) $request->input('links');

            $links = array_unique(array_diff($links, ['']));

            /** @var Load $category */
            $category = Load::query()->find($cid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['category' => __('loads.load_closed')]);

                $duplicate = Down::query()->where('title', $title)->count();
                $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);
            }

            $validator->notEmpty($files || $links, ['files' => __('validator.file_upload_one')]);
            $validator->lte(count($files) + count($links), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $allowExtension = explode(',', setting('allowextload'));

                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => $allowExtension,
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
                }

                foreach ($links as $link) {
                    $validator->length($link, 5, 100, ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);

                    if (! in_array(getExtension($link), $allowExtension, true)) {
                        $validator->addError(['links' => __('validator.extension')]);
                    }
                }
            }

            if ($validator->isValid()) {
                /** @var Down $down */
                $down = Down::query()->create([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                    'active'      => isAdmin(User::ADMIN),
                    'links'       => $links ? array_values($links) : null,
                ]);

                foreach ($files as $file) {
                    $down->uploadAndConvertFile($file);
                }

                if (isAdmin(User::ADMIN)) {
                    $down->category->increment('count_downs');
                    clearCache(['statLoads', 'recentDowns']);
                } else {
                    $admins = User::query()->whereIn('level', [User::BOSS, User::ADMIN])->get();

                    if ($admins->isNotEmpty()) {
                        $text = textNotice('down_upload', ['url' => '/admin/downs/edit/' . $down->id, 'title' => $down->title]);

                        /** @var User $admin */
                        foreach ($admins as $admin) {
                            $admin->sendMessage($user, $text, false);
                        }
                    }
                }

                $flood->saveState();
                setFlash('success', __('loads.down_added_success'));

                return redirect('downs/' . $down->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $down = new Down();

        return view('loads/create', compact('categories', 'down', 'cid'));
    }

    /**
     * Редактирование загрузки
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $cid = int($request->input('category'));

        /** @var Down $down */
        $down = Down::query()->where('user_id', getUser('id'))->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($down->active) {
            abort(200, __('loads.down_verified'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $files = (array) $request->file('files');
            $links = (array) $request->input('links');

            $links = array_unique(array_diff($links, ['']));

            /** @var Load $category */
            $category = Load::query()->find($cid);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')])
                ->empty($category->closed, ['category' => __('loads.load_closed')]);

            $duplicate = Down::query()->where('title', $title)->where('id', '<>', $down->id)->count();
            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);

            $existFiles = $down->files ? $down->files->count() : 0;
            $validator->notEmpty(count($files) + count($links) + $existFiles , ['files' => __('validator.file_upload_one')]);
            $validator->lte(count($files) + count($links) + $existFiles, setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $allowExtension = explode(',', setting('allowextload'));

                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => $allowExtension,
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
                }

                foreach ($links as $link) {
                    $validator->length($link, 5, 100, ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);

                    if (! in_array(getExtension($link), $allowExtension, true)) {
                        $validator->addError(['links' => __('validator.extension')]);
                    }
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

                foreach ($files as $file) {
                    $down->uploadAndConvertFile($file);
                }

                clearCache(['statLoads', 'recentDowns']);
                setFlash('success', __('loads.down_edited_success'));

                return redirect('downs/' . $down->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('loads/edit', compact('categories', 'down', 'cid'));
    }

    /**
     * Удаление файла
     *
     * @param int $id
     * @param int $fid
     *
     * @return RedirectResponse
     */
    public function deleteFile(int $id, int $fid): RedirectResponse
    {
        /** @var Down $down */
        $down = Down::query()->where('user_id', getUser('id'))->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        /** @var File $file */
        $file = $down->files()->find($fid);

        if (! $file) {
            abort(404, __('loads.down_not_exist'));
        }

        deleteFile(public_path($file->hash));

        setFlash('success', __('loads.file_deleted_success'));
        $file->delete();

        return redirect('downs/edit/' . $down->id);
    }

    /**
     * Скачивание файла
     *
     * @param int       $id
     * @param Validator $validator
     *
     * @return Response
     */
    public function download(int $id, Validator $validator): Response
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! (getUser() || setting('down_guest_download'))) {
            abort(403, __('loads.download_authorized'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        $validator->true(file_exists(public_path($file->hash)), __('loads.down_not_exist'));

        if ($validator->isValid()) {
            Reader::countingStat($file->relate);

            return response()->download(public_path($file->hash), $file->name);
        }

        setFlash('danger', $validator->getErrors());

        return redirect('downs/' . $file->relate->id);
    }

    /**
     * Комментарии
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return View|RedirectResponse
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $down->active) {
            abort(200, __('loads.down_not_verified'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            if ($validator->isValid()) {
                /** @var Comment $comment */
                $comment = $down->comments()->create([
                    'text'        => antimat($msg),
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $down->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/downs/comment/' . $down->id . '/' . $comment->id, $down->title);

                setFlash('success', __('main.comment_added_success'));

                return redirect('downs/end/' . $down->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $down->comments()
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('loads/comments', compact('down', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     *
     * @param int       $id
     * @param int       $cid
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
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
            $msg  = $request->input('msg');
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

                return redirect('downs/comments/' . $id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('loads/editcomment', compact('down', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function end(int $id): RedirectResponse
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $total = $down->comments()->count();

        $end = ceil($total / setting('comments_per_page'));

        return redirect('downs/comments/' . $down->id . '?page=' . $end);
    }

    /**
     * Просмотр zip архива
     *
     * @param int $id
     *
     * @return View
     */
    public function zip(int $id): View
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        if ($file->extension !== 'zip') {
            abort(200, __('loads.archive_only_zip'));
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(public_path($file->hash));
            $entries = array_values($archive->getEntries());

            $down      = $file->relate;
            $viewExt   = Down::getViewExt();
            $documents = paginate($entries, setting('ziplist'));

            $archive->close();
        } catch (ZipException) {
            abort(200, __('loads.archive_not_open'));
        }

        return view('loads/zip', compact('down', 'file', 'documents', 'viewExt'));
    }

    /**
     * Просмотр файла в zip архиве
     *
     * @param int $id
     * @param int $fid
     *
     * @return View
     */
    public function zipView(int $id, int $fid): View
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort(200, __('loads.down_not_verified'));
        }

        if ($file->extension !== 'zip') {
            abort(200, __('loads.archive_only_zip'));
        }

        try {
            $viewExt = Down::getViewExt();

            $archive = new ZipFile();
            $archive->openFile(public_path($file->hash));
            $entries = array_values($archive->getEntries());

            $document = $entries[$fid] ?? null;
            $ext      = getExtension($document->getName());
            $content  = $archive[$document->getName()];
            $archive->close();

            if (! in_array($ext, $viewExt, true)) {
                abort(200, __('loads.file_not_read'));
            }

            if (
                $document->getUncompressedSize() > 0
                && preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $document->getName())
            ) {
                $ext = getExtension($document->getName());

                header('Content-type: image/' . $ext);
                header('Content-Length: ' . strlen($content));
                header('Content-Disposition: inline; filename="' . $document->getName() . '";');
                exit($content);
            }

            if (! isUtf($content)) {
                $content = winToUtf($content);
            }

            $down = $file->relate;
        } catch (ZipException) {
            abort(200, __('loads.file_not_read'));
        }

        return view('loads/zip_view', compact('down', 'file', 'document', 'content'));
    }

    /**
     * RSS комментариев
     *
     * @param int $id
     *
     * @return View
     */
    public function rss(int $id): View
    {
        $down = Down::query()->where('id', $id)->with('lastComments')->first();

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        return view('loads/rss_comments', compact('down'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     *
     * @return RedirectResponse
     */
    public function viewComment(int $id, int $cid): RedirectResponse
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $total = $down->comments()
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));

        return redirect('downs/comments/' . $down->id . '?page=' . $end . '#comment_' . $cid);
    }
}
