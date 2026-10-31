<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\CategoryTree;
use Illuminate\Database\Eloquent\Collection;

trait CategoryTreeTrait
{
    /**
     * Цепочка от корня до самого раздела — для хлебных крошек
     *
     * Подъём останавливается на уже пройденном разделе: кольцо, записанное
     * в базу до проверки родителя в формах, иначе уводило в бесконечную рекурсию
     */
    public function getParents(): Collection
    {
        $chain = [];
        $seen = [];
        $category = $this;

        while ($category->getKey() && ! isset($seen[$category->getKey()])) {
            $seen[$category->getKey()] = true;
            $chain[] = $category;
            $category = $category->parent;
        }

        return Collection::make(array_reverse($chain));
    }

    /**
     * Get children
     */
    public function getChildren(): Collection
    {
        $categories = $this::query()
            ->orderBy('sort')
            ->get();

        return CategoryTree::flatten($categories);
    }
}
