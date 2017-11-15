<?php

namespace App\Controllers\Load;

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
            ->leftJoin('pollings', function ($join) {
                $join->on('downs.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Down::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('category.parent')
            ->first();

        if (! $down) {
            abort(404, 'Данная загрузка не найдена!');
        }

        if (! $down->active && $down->user_id == getUser('id')) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        $ext      = getExtension($down->link);
        $filesize = $down->link ? formatFileSize(UPLOADS . '/files/' . $down->folder . $down->link) : 0;
        $rating   = $down->rated ? round($down->rating / $down->rated, 1) : 0;

        return view('load/down', compact('down', 'ext', 'filesize', 'rating'));
    }

    /**
     * Голосование
     */
    public function vote($id)
    {
        $token = check(Request::input('token'));
        $score = abs(intval(Request::input('score')));

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
        $protect = check(Request::input('protect'));

        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        $validator = new Validator();
        $validator
            ->true(file_exists(UPLOADS . '/files/' . $down->folder . $down->link), 'Файла для скачивания не существует!')
            ->true(getUser() || $protect === $_SESSION['protect'], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
            ->notEmpty($down->active, 'Данный файл еще не проверен модератором!');

        if ($validator->isValid()) {

            $reads = Read::query()
                ->where('relate_type', Down::class)
                ->where('relate_id', $down->id)
                ->where('ip', getIp())
                ->first();

            if (! $reads) {
                Read::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $down->id,
                    'ip'          => getIp(),
                    'created_at'  => SITETIME,
                ]);

                $down->increment('loads');
            }

            redirect('/uploads/files/' . $down->folder . $down->link);
        } else {
            setFlash('danger', $validator->getErrors());
            redirect('/down/' . $down->id);
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

                $down->increment('comments');

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/down/' . $down->id . '/end');
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
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('load/comments', compact('down', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment($id, $cid)
    {
        $page = abs(intval(Request::input('page', 1)));

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
            $page  = abs(intval(Request::input('page', 1)));

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
                redirect('/down/' . $id . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('load/editcomment', compact('comment', 'page'));
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
        redirect('/down/' . $down->id . '/comments?page=' . $end);
    }

    /**
     * Просмотр zip архива
     */
    public function zip($id)
    {
        $down = Down::query()->find($id);
        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $down->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if (getExtension($down->link) != 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(UPLOADS . '/files/' . $down->folder . $down->link);
        } catch (Exception $e) {
            abort('default', 'Не удалось открыть архив!');
        }

        $page = paginate(setting('ziplist'), $archive->count());
        $getFiles = array_values($archive->getAllInfo());

        $viewExt = Down::getViewExt();
        $files = array_slice($getFiles, $page['offset'], $page['limit'], true);

        return view('load/zip', compact('down', 'files', 'page', 'viewExt', 'archive'));
    }

    /**
     * Просмотр файла в zip архиве
     */
    public function zipView($id, $fid)
    {
        $down = Down::query()->find($id);
        if (! $down) {
            abort(404, 'Данного файла не существует!');
        }

        if (! $down->active) {
            abort('default', 'Данный файл еще не проверен модератором!');
        }

        if (getExtension($down->link) != 'zip') {
            abort('default', 'Просматривать можно только ZIP архивы!');
        }

        try {
            $archive = new ZipFile();
            $archive->openFile(UPLOADS . '/files/' . $down->folder . $down->link);
        } catch (Exception $e) {
            abort('default', 'Не удалось открыть архив!');
        }

        $getFiles = array_values($archive->getAllInfo());
        $file     = $getFiles[$fid] ?? null;

        if (! $file) {
            abort('default', 'Не удалось вывести содержимое файла');
        }

        $content = $archive[$file->getName()];

        if (preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $file->getName()) && $file->getSize() > 0) {

            $ext = getExtension($file->getName());

            header('Content-type: image/' . $ext);
            header('Content-Length: ' . strlen($content));
            header('Content-Disposition: inline; filename="' . $file->getName() . '";');
            exit($content);
        }

        if (! isUtf($content)) {
            $content = winToUtf($content);
        }

        return view('load/zip_view', compact('down', 'file', 'content'));
    }
}
