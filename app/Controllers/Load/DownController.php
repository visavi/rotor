<?php

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
use App\Models\Polling;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use PhpZip\ZipFile;

class DownController extends BaseController
{
    /**
     * Просмотр загрузки
     *
     * @param int $id
     * @return string
     */
    public function index(int $id): string
    {
        $down = Down::query()
            ->select('downs.*', 'pollings.vote')
            ->where('downs.id', $id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('downs.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Down::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $down) {
            abort(404, 'Данная загрузка не найдена!');
        }

        if (! isAdmin(User::ADMIN) && (! $down->active && $down->user_id !== getUser('id'))) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0;

        return view('loads/down', compact('down', 'rating'));
    }

    /**
     * Редактирование загрузки
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Down $down */
        $down = Down::query()->where('user_id', getUser('id'))->find($id);

        if (! $down) {
            abort(404, 'Файла не существует или вы не автор данной загрузки!');
        }

        if ($down->active) {
            abort('default', 'Данный файл уже проверен модератором!');
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $files = (array) $request->file('files');

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 50, 5000, ['text' => 'Слишком длинное или короткое описание!']);

            $duplicate = Down::query()->where('title', $title)->where('id', '<>', $down->id)->count();
            $validator->empty($duplicate, ['title' => 'Загрузка с аналогичный названием уже существует!']);

            $existFiles = $down->files ? $down->files->count() : 0;
            $validator->notEmpty(\count($files) + $existFiles, ['files' => 'Необходимо загрузить хотя бы 1 файл']);
            $validator->lte(\count($files) + $existFiles, setting('maxfiles'), ['files' => 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов']);

            if ($validator->isValid()) {

                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => 'Не удалось загрузить файл!']);
                }
            }

            if ($validator->isValid()) {

                $down->update([
                    'title' => $title,
                    'text'  => $text,
                ]);

                foreach ($files as $file) {
                    $down->uploadFile($file);
                }

                setFlash('success', 'Загрузка успешно отредактирована!');
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
     * @throws Exception
     */
    public function deleteFile(int $id, int $fid): void
    {
        /** @var Down $down */
        $down = Down::query()->where('user_id', getUser('id'))->find($id);

        if (! $down) {
            abort(404, 'Файла не существует или вы не автор данной загрузки!');
        }

        /** @var File $file */
        $file = File::query()->where('relate_id', $down->id)->find($fid);

        if (! $file) {
            abort(404, 'Файла не существует!');
        }

        deleteFile(HOME . $file->hash);

        setFlash('success', 'Файл успешно удален!');
        $file->delete();

        redirect('/downs/edit/' . $down->id);
    }

    /**
     * Создание загрузки
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        $cid = int($request->input('cid'));

        if (! setting('downupload')) {
            abort('default', 'Загрузка файлов запрещена администрацией сайта!');
        }

        if (! $user = getUser()) {
            abort(403);
        }

        $loads = Load::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($loads->isEmpty()) {
            abort('default', 'Разделы загрузок еще не созданы!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $files = (array) $request->file('files');

            /** @var Load $category */
            $category = Load::query()->find($cid);

            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 50, 5000, ['text' => 'Слишком длинный или короткий текст описания!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять файлы раз в ' . Flood::getPeriod() . ' секунд!'])
                ->notEmpty($category, ['category' => 'Категории для данного файла не существует!']);

            if ($category) {
                $validator->empty($category->closed, ['category' => 'В данный раздел запрещено публиковать файлы!']);

                $duplicate = Down::query()->where('title', $title)->count();
                $validator->empty($duplicate, ['title' => 'Загрузка с аналогичный названием уже существует!']);
            }

            $validator->notEmpty($files, ['files' => 'Необходимо загрузить хотя бы 1 файл']);
            $validator->lte(\count($files), setting('maxfiles'), ['files' => 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов']);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => 'Не удалось загрузить файл!']);
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
                } else {
                    $admins = User::query()->whereIn('level', [User::BOSS, User::ADMIN])->get();

                    if ($admins->isNotEmpty()) {
                        $text = 'Уведомеление о публикации файла.' . PHP_EOL . 'Новый файл [b][url=/admin/downs/edit/' . $down->id . ']' . $down->title . '[/url][/b] требует подтверждения на публикацию!';

                        foreach ($admins as $admin) {
                            $admin->sendMessage($user, $text);
                        }
                    }
                }

                setFlash('success', 'Файл успешно загружен!');
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
     * @return void
     */
    public function vote(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $score = int($request->input('score'));

        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $validator
            ->equal($token, $_SESSION['token'], ['score' => 'Неверный идентификатор сессии, повторите действие!'])
            ->true(getUser(), ['score' => 'Для голосования необходимо авторизоваться!'])
            ->between($score, 1, 5, ['score' => 'Необходимо поставить оценку!'])
            ->notEmpty($down->active, ['score' => 'Данный файл еще не проверен модератором!'])
            ->notEqual($down->user_id, getUser('id'), ['score' => 'Нельзя голосовать за свой файл!']);

        if ($validator->isValid()) {

            $polling = Polling::query()
                ->where('relate_type', Down::class)
                ->where('relate_id', $down->id)
                ->where('user_id', getUser('id'))
                ->first();

            if ($polling) {

                $down->increment('rating', $score - $polling->vote);

                $polling->update([
                    'vote'       => $score,
                    'created_at' => SITETIME
                ]);

            } else {
                Polling::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $down->id,
                    'user_id'     => getUser('id'),
                    'vote'        => $score,
                    'created_at'  => SITETIME,
                ]);

                $down->update([
                    'rating' => DB::connection()->raw('rating + ' . $score),
                    'rated'  => DB::connection()->raw('rated + 1'),
                ]);
            }

            setFlash('success', 'Оценка успешно принята!');
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
     * @return void
     */
    public function download(int $id, Validator $validator): void
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        $validator->true(file_exists(HOME . $file->hash), 'Файла для скачивания не существует!');

        if ($validator->isValid()) {
            $reader = Reader::query()
                ->where('relate_type', Down::class)
                ->where('relate_id', $file->relate->id)
                ->where('ip', getIp())
                ->first();

            if (! $reader) {
                Reader::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $file->relate->id,
                    'ip'          => getIp(),
                    'created_at'  => SITETIME,
                ]);

                $file->relate->increment('loads');
            }

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
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator): string
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $down->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator
                ->true(getUser(), 'Для добавления комментария необходимо авторизоваться!')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткий комментарий!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять комментарии раз в ' . Flood::getPeriod() . ' секунд!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = Comment::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $down->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $down->increment('count_comments');

                sendNotify($msg, '/downs/comment/' . $down->id . '/' . $comment->id, $down->title);

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/downs/end/' . $down->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', Down::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('downcomm'), $total);

        $comments = Comment::query()
            ->where('relate_type', Down::class)
            ->where('relate_id', $id)
            ->orderBy('created_at')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('loads/comments', compact('down', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     *
     * @param int       $id
     * @param int       $cid
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): string
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $page = int($request->input('page', 1));

        if (! getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->where('relate_type', Down::class)
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));
            $page  = int($request->input('page', 1));

            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
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
     * @return void
     */
    public function end(int $id): void
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Down::class)
            ->where('relate_id', $down->id)
            ->count();

        $end = ceil($total / setting('downcomm'));
        redirect('/downs/comments/' . $down->id . '?page=' . $end);
    }

    /**
     * Просмотр zip архива
     *
     * @param int $id
     * @return string
     */
    public function zip(int $id): string
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if ($file->extension !== 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(HOME . $file->hash);

            $down         = $file->relate;
            $page         = paginate(setting('ziplist'), $archive->count());
            $getDocuments = array_values($archive->getAllInfo());

            $viewExt   = Down::getViewExt();
            $documents = \array_slice($getDocuments, $page->offset, $page->limit, true);

        } catch (Exception $e) {
            abort('default', 'Не удалось открыть архив!');
        }

        return view('loads/zip', compact('down', 'file', 'documents', 'page', 'viewExt'));
    }

    /**
     * Просмотр файла в zip архиве
     *
     * @param int $id
     * @param int $fid
     * @return string
     */
    public function zipView(int $id, int $fid): string
    {
        /** @var File $file */
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $file->relate->active && ! isAdmin(User::ADMIN)) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if ($file->extension !== 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(HOME . $file->hash);

            /** @var ZipFile $archive */
            $getDocuments = array_values($archive->getAllInfo());
            $document     = $getDocuments[$fid] ?? null;

            $content = $archive[$document->getName()];

            if ($document->getSize() > 0 && preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $document->getName())) {

                $ext = getExtension($document->getName());

                header('Content-type: image/' . $ext);
                header('Content-Length: ' . \strlen($content));
                header('Content-Disposition: inline; filename="' . $document->getName() . '";');
                exit($content);
            }

            if (! isUtf($content)) {
                $content = winToUtf($content);
            }

            $down = $file->relate;
        } catch (Exception $e) {
            abort('default', 'Не удалось прочитать файл!');
        }

        return view('loads/zip_view', compact('down', 'file', 'document', 'content'));
    }

    /**
     * RSS комментариев
     *
     * @param int $id
     * @return string
     */
    public function rss(int $id): string
    {
        $down = Down::query()->where('id', $id)->with('lastComments')->first();

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        return view('loads/rss_comments', compact('down'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     * @return void
     */
    public function viewComment(int $id, int $cid): void
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Down::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('downcomm'));
        redirect('/downs/comments/' . $down->id . '?page=' . $end . '#comment_' . $cid);
    }
}
