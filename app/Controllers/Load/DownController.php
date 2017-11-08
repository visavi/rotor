<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Down;
use App\Models\Load;
use App\Models\Polling;
use Illuminate\Database\Capsule\Manager as DB;

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
        $filesize = $down->link ? formatFileSize(UPLOADS.'/files/'.$down->folder.$down->link) : 0;
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
        $validator->equal($token, $_SESSION['token'], ['score' => 'Неверный идентификатор сессии, повторите действие!'])
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
                    'vote'        => $score,
                    'created_at'  => SITETIME
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
            ->true(file_exists(UPLOADS.'/files/'.$down->folder.$down->link), 'Файла для скачивания не существует!')
            ->true(getUser() || $protect === $_SESSION['protect'], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
            ->notEmpty($down->active, 'Данный файл еще не проверен модератором!');

        if ($validator->isValid()) {

            $load = Load::query()
                ->where('down_id', $down->id)
                ->where('ip', getIp())
                ->first();

            if (! $load) {
                Load::query()->create([
                    'down_id'    => $down->id,
                    'ip'         => getIp(),
                    'created_at' => SITETIME,
                ]);

                $down->increment('loads');
            }

            redirect('/uploads/files/'.$down->folder.$down->link);
        } else {
            setFlash('danger', $validator->getErrors());
            redirect('/down/' . $down->id);
        }
    }
}
