<?php

namespace App\Controllers\Load;

use App\Models\File;
use App\Models\Load;
use Exception;
use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Flood;
use App\Models\Read;
use App\Models\Polling;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use PhpZip\ZipFile;

class DownController extends BaseController
{
    /**
     * Просмотр загрузки
     */
    public function index($id)
    {
        $down = Down::query()
            ->select('downs.*', 'pollings.vote')
            ->where('downs.id', $id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('downs.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Down::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $down) {
            abort(404, 'Данная загрузка не найдена!');
        }

        if (! $down->active && $down->user_id !== getUser('id')) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0;

        if ($down->files->isNotEmpty()) {

            $files  = [];
            $images = [];

            foreach ($down->files as $file) {

                if ($file->isImage()) {
                    $images[] = $file;
                } else {
                    $files[] = $file;
                }
            }
        }

        return view('load/down', compact('down', 'rating', 'files', 'images'));
    }

    /**
     * Создание загрузки
     */
    public function create()
    {
        $cid = int(Request::input('cid'));

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

        if (Request::isMethod('post')) {

            $token    = check(Request::input('token'));
            $category = check(Request::input('category'));
            $title    = check(Request::input('title'));
            $text     = check(Request::input('text'));
            $file     = Request::file('file');
            $images   = (array) Request::file('images');

            $category = Load::query()->find($category);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 50, 5000, ['text' => 'Слишком длинный или короткий текст описания!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять файлы раз в ' . Flood::getPeriod() . ' секунд!'])
                ->notEmpty($category, ['category' => 'Категории для данного файла не существует!']);

            if ($category) {
                $validator->empty($category->closed, ['category' => 'В данный раздел запрещено загружать файлы!']);

                $downCount = Down::query()->where('title', $title)->count();
                $validator->false($downCount, ['title' => 'Файл с аналогичный названием уже имеется в загрузках!']);
            }

            $validator->lte(count($images), 5, ['file' => 'Разрешено загружать не более 5 скриншотов']);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'maxweight'  => setting('screenupsize'),
                    'minweight'  => 100,
                ];

                $validator->file($file, $rules, ['file' => 'Не удалось загрузить файл!']);

                if ($images) {
                    foreach ($images as $image) {
                        $validator->file($image, $rules, ['images' => 'Не удалось загрузить файл!']);
                    }
                }
            }

            if ($validator->isValid()) {

                $down = Down::query()->create([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                ]);

                $fileName = uniqueName($file->getClientOriginalExtension());
                $file->move(UPLOADS . '/files/', $fileName);

                File::query()->create([
                    'relate_id'   => $down->id,
                    'relate_type' => Down::class,
                    'hash'        => $fileName,
                    'name'        => $file->getClientOriginalName(),
                    'size'        => $file->getClientSize(),
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                ]);

                if ($images) {
                    foreach ($images as $image) {
                        $fileName = uploadImage($image, UPLOADS . '/files/');

                        File::query()->create([
                            'relate_id'   => $down->id,
                            'relate_type' => Down::class,
                            'hash'        => $fileName,
                            'name'        => $image->getClientOriginalName(),
                            'size'        => $image->getClientSize(),
                            'user_id'     => $user->id,
                            'created_at'  => SITETIME,
                        ]);
                    }
                }

                setFlash('success', 'Файл успешно загружен!');
                redirect('/down/' . $down->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('load/create', compact('loads', 'cid'));
    }

    /**
     * Голосование
     */
    public function vote($id)
    {
        $token = check(Request::input('token'));
        $score = int(Request::input('score'));

        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $validator = new Validator();
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
                    'rating' => DB::raw('rating + ' . $score),
                    'rated'  => DB::raw('rated + 1'),
                ]);
            }

            setFlash('success', 'Оценка успешно принята!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/down/' . $down->id);
    }

    /**
     * Скачивание файла
     */
    public function download($id)
    {
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        $validator = new Validator();
        $validator
            ->true(file_exists(UPLOADS . '/files/' . $file->hash), 'Файла для скачивания не существует!')
            ->notEmpty($file->relate->active, 'Данный файл еще не проверен модератором!');

        if ($validator->isValid()) {

            $reads = Read::query()
                ->where('relate_type', Down::class)
                ->where('relate_id', $file->relate->id)
                ->where('ip', getIp())
                ->first();

            if (! $reads) {
                Read::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $file->relate->id,
                    'ip'          => getIp(),
                    'created_at'  => SITETIME,
                ]);

                $file->relate->increment('loads');
            }

            redirect('/uploads/files/' . $file->hash);
        } else {
            setFlash('danger', $validator->getErrors());
            redirect('/down/' . $file->relate->id);
        }
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $down->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator
                ->true(getUser(), 'Для добавления комментария необходимо авторизоваться!')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткий комментарий!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять комментарии раз в ' . Flood::getPeriod() . ' секунд!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                Comment::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $down->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $down->increment('count_comments');

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/down/end/' . $down->id);
            } else {
                setInput(Request::all());
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

        return view('load/comments', compact('down', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment($id, $cid)
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $page = int(Request::input('page', 1));

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

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $page  = int(Request::input('page', 1));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/down/comments/' . $id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('load/editcomment', compact('down', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Down::class)
            ->where('relate_id', $down->id)
            ->count();

        $end = ceil($total / setting('downcomm'));
        redirect('/down/comments/' . $down->id . '?page=' . $end);
    }

    /**
     * Просмотр zip архива
     */
    public function zip($id)
    {
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $file->relate->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if ($file->extension !== 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(UPLOADS . '/files/' . $file->hash);
        } catch (Exception $e) {
            abort('default', 'Не удалось открыть архив!');
        }

        $page         = paginate(setting('ziplist'), $archive->count());
        $getDocuments = array_values($archive->getAllInfo());

        $viewExt   = Down::getViewExt();
        $documents = array_slice($getDocuments, $page->offset, $page->limit, true);

        return view('load/zip', compact('file', 'documents', 'page', 'viewExt'));
    }

    /**
     * Просмотр файла в zip архиве
     */
    public function zipView($id, $fid)
    {
        $file = File::query()->where('relate_type', Down::class)->find($id);

        if (! $file || ! $file->relate) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $file->relate->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if ($file->extension !== 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(UPLOADS . '/files/' . $file->hash);
        } catch (Exception $e) {
            abort('default', 'Не удалось открыть архив!');
        }

        $getDocuments = array_values($archive->getAllInfo());
        $document     = $getDocuments[$fid] ?? null;

        if (! $document) {
            abort('default', 'Не удалось вывести содержимое файла');
        }

        $content = $archive[$document->getName()];

        if (preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $document->getName()) && $document->getSize() > 0) {

            $ext = getExtension($document->getName());

            header('Content-type: image/' . $ext);
            header('Content-Length: ' . strlen($content));
            header('Content-Disposition: inline; filename="' . $document->getName() . '";');
            exit($content);
        }

        if (! isUtf($content)) {
            $content = winToUtf($content);
        }

        return view('load/zip_view', compact('file', 'document', 'content'));
    }

    /**
     * RSS комментариев
     */
    public function rss($id)
    {
        $down = Down::query()->where('id', $id)->with('lastComments')->first();

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        return view('load/rss_comments', compact('down'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $cid)
    {
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
        redirect('/down/comments/' . $id . '?page=' . $end . '#comment_' . $cid);
    }
}
