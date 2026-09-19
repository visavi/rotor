<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\CategoryTree;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait CategoryTreeTrait
{
    /**
     * Get parents
     */
    public function getParents(): Collection
    {
        $flat = $this->buildParentCategoriesFlat($this);

        return Collection::make($flat);
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

    /**
     * Build parent categories flat
     *
     * @param self $category
     */
    private function buildParentCategoriesFlat(Model $category, array &$tree = []): array
    {
        if ($category->parent->id) {
            $this->buildParentCategoriesFlat($category->parent, $tree);
        }

        $tree[] = $category;

        return $tree;
    }
}
