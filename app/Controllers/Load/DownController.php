<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\Down;

class DownController extends BaseController
{
    /**
     * Просмотр загрузки
     */
    public function index($id)
    {
        $down = Down::query()
            ->where('id', $id)
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
}
