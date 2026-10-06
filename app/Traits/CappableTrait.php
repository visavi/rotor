<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait CappableTrait
{
    /**
     * Оставляет первые $max записей в текущем порядке — вызывать после orderBy, перед paginate.
     * Подсчёт страниц идёт по ним, а не по всей таблице, и в память грузится только страница.
     * Общие ленты дальше нескольких страниц не листают, порядок задаёт сортировка
     */
    public function scopeCapped(Builder $query, int $max = 1000): Builder
    {
        $model = $query->getModel();
        // LIMIT в подзапросе IN MySQL не принимает — через производную таблицу
        $first = $query->clone()->select($model->getQualifiedKeyName())->limit($max)->toBase();

        return $query->whereIn($model->getQualifiedKeyName(), DB::query()->fromSub($first, 'capped')->select($model->getKeyName()));
    }
}
