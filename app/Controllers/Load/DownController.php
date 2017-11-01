<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Down;
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
        $folder   = $down->category->folder ? $down->category->folder.'/' : '';
        $filesize = $down->link ? formatFileSize(UPLOADS.'/files/'.$folder.$down->link) : 0;
        $rating   = $down->rated ? round($down->rating / $down->rated, 1) : 0;

        return view('load/down', compact('down', 'ext', 'folder', 'filesize', 'rating'));
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
            ->between($score, 1, 5, ['score' => 'Необходимо поставить оценку от 1 до 5!'])
            ->notEmpty($down->active, ['score' => 'Данный файл еще не проверен модератором!']);
            //->notEqual($down->user_id, getUser('id'), ['score' => 'Нельзя голосовать за свой файл!']);

        if ($validator->isValid()) {

            $expiresRating = SITETIME + 3600 * 24 * 365;

            Polling::query()
                ->where('relate_type', Down::class)
                ->where('created_at', '<', SITETIME)
                ->delete();


            $polling = Polling::query()
                ->where('relate_type', Down::class)
                ->where('relate_id', $down->id)
                ->where('user_id', getUser('id'))
                ->first();

            if ($polling) {

                $down->increment('rating', $score - $polling->vote);

                $polling->update([
                    'vote'        => $score,
                    'created_at'  => $expiresRating
                ]);

            } else {
                Polling::query()->create([
                    'relate_type' => Down::class,
                    'relate_id'   => $down->id,
                    'user_id'     => getUser('id'),
                    'vote'        => $score,
                    'created_at'  => $expiresRating,
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
}
