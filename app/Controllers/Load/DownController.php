<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Models\File;
use App\Models\Load;
use App\Models\User;
use Exception;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Flood;
use App\Models\Reader;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use PhpZip\ZipFile;

class DownController extends BaseController
{
    /**
     * Просмотр загрузки
     *
     * @param int $id
     *
     * @return string
     */
    public function index(int $id): string
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
            abort('default', __('loads.down_not_verified'));
        }

        return view('loads/down', compact('down'));
    }

    /**
     * Редактирование загрузки
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Down $down */
        $down = Down::query()->where('user_id', getUser('id'))->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($down->active) {
            abort('default', __('loads.down_verified'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $files = (array) $request->file('files');

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')]);

            $duplicate = Down::query()->where('title', $title)->where('id', '<>', $down->id)->count();
            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);

            $existFiles = $down->files ? $down->files->count() : 0;
            $validator->notEmpty(count($files) + $existFiles, ['files' => __('validator.file_upload_one')]);
            $validator->lte(count($files) + $existFiles, setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
                }
            }

            if ($validator->isValid()) {
                $down->update([
                    'title' => $title,
                    'text'  => $text,
                ]);

                foreach ($files as $file) {
                    $down->uploadAndConvertFile($file);
                }

                clearCache(['statLoads', 'recentDowns']);
                setFlash('success', __('loads.down_edited_success'));
                redirect('/downs/' . $down->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/edit', compact('down'));
    }

    /**
     * Удаление файла
     *
     * @param int $id
     * @param int $fid
     *
     * @throws Exception
     */
    public function deleteFile(int $id, int $fid): void
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

        deleteFile(HOME . $file->hash);

        setFlash('success', __('loads.file_deleted_success'));
        $file->delete();

        redirect('/downs/edit/' . $down->id);
    }

    /**
     * Создание загрузки
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
    {
        $cid = int($request->input('cid'));

        if (! isAdmin() && ! setting('downupload')) {
            abort('default', __('loads.down_closed'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $loads = Load::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($loads->isEmpty()) {
            abort('default', __('loads.empty_loads'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $files = (array) $request->file('files');

            /** @var Load $category */
            $category = Load::query()->find($cid);

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['category' => __('loads.load_closed')]);

                $duplicate = Down::query()->where('title', $title)->count();
                $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);
            }

            $validator->notEmpty($files, ['files' => __('validator.file_upload_one')]);
            $validator->lte(count($files), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
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

                        foreach ($admins as $admin) {
                            $admin->sendMessage($user, $text);
                        }
                    }
                }

                $flood->saveState();

                setFlash('success', __('loads.file_uploaded_success'));
                redirect('/downs/' . $down->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/create', compact('loads', 'cid'));
    }

    /**
     * Голосование
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function vote(int $id, Request $request, Validator $validator): void
    {
        $score = int($request->input('score'));

        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $validator
            ->equal($request->input('token'), $_SESSION['token'], ['score' => __('validator.token')])
            ->true(getUser(), ['score' => __('main.not_authorized')])
            ->between($score, 1, 5, ['score' => __('loads.down_voted_required')])
            ->notEmpty($down->active, ['score' => __('loads.down_not_verified')])
            ->notEqual($down->user_id, getUser('id'), ['score' => __('loads.down_voted_forbidden')]);

        if ($validator->isValid()) {
            $polling = $down->polling()->first();
            if ($polling) {
                $down->increment('rating', $score - $polling->vote);

                $polling->update([
                    'vote'       => $score,
                    'created_at' => SITETIME
                ]);
            } else {
                $down->polling()->create([
                    'user_id'     => getUser('id'),
                    'vote'        => $score,
                    'created_at'  => SITETIME,
                ]);

                $down->increment('rating', $score);
                $down->increment('rated');
            }

            setFlash('success', __('loads.down_voted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/downs/' . $down->id);
    }

    /**
     * Скачивание файла
     *
     * @param int       $id
     * @param Validator $validator
     *
     * @return void
     */
    public function download(int $id, Validator $validator): void
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', __('loads.down_not_verified'));
        }

        $validator->true(file_exists(HOME . $file->hash), __('loads.down_not_exist'));

        if ($validator->isValid()) {
            Reader::countingStat($file->relate);
            $file->download();
        } else {
            setFlash('danger', $validator->getErrors());
            redirect('/downs/' . $file->relate->id);
        }
    }

    /**
     * Комментарии
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): string
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $down->active) {
            abort('default', __('loads.down_not_verified'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
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
                redirect('/downs/end/' . $down->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
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
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): string
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
            abort('default', __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg  = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));
                redirect('/downs/comments/' . $id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('loads/editcomment', compact('down', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     *
     * @return void
     */
    public function end(int $id): void
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $total = $down->comments()->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/downs/comments/' . $down->id . '?page=' . $end);
    }

    /**
     * Просмотр zip архива
     *
     * @param int $id
     *
     * @return string
     */
    public function zip(int $id): string
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', __('loads.down_not_verified'));
        }

        if ($file->extension !== 'zip') {
            abort('default', __('loads.archive_only_zip'));
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(HOME . $file->hash);

            $down         = $file->relate;
            $getDocuments = array_values($archive->getAllInfo());
            $viewExt      = Down::getViewExt();

            $documents = paginate($getDocuments, setting('ziplist'));
        } catch (Exception $e) {
            abort('default', __('loads.archive_not_open'));
        }

        return view('loads/zip', compact('down', 'file', 'documents', 'viewExt'));
    }

    /**
     * Просмотр файла в zip архиве
     *
     * @param int $id
     * @param int $fid
     *
     * @return string
     */
    public function zipView(int $id, int $fid): string
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::$morphName)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', __('loads.down_not_verified'));
        }

        if ($file->extension !== 'zip') {
            abort('default', __('loads.archive_only_zip'));
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(HOME . $file->hash);
            $getDocuments = array_values($archive->getAllInfo());
            $document     = $getDocuments[$fid] ?? null;

            $content = $archive[$document->getName()];

            if ($document->getSize() > 0 && preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $document->getName())) {
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
        } catch (Exception $e) {
            abort('default', __('loads.file_not_read'));
        }

        return view('loads/zip_view', compact('down', 'file', 'document', 'content'));
    }

    /**
     * RSS комментариев
     *
     * @param int $id
     *
     * @return string
     */
    public function rss(int $id): string
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
     * @return void
     */
    public function viewComment(int $id, int $cid): void
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
        redirect('/downs/comments/' . $down->id . '?page=' . $end . '#comment_' . $cid);
    }
}
