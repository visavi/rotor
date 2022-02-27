<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait CategoryTreeTrait
{
    /**
     * Get parents
     *
     * @return Collection
     */
    public function getParents(): Collection
    {
        $flat = $this->buildParentCategoriesFlat($this);

        return Collection::make($flat);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren(): Collection
    {
        $categories = $this::query()
            ->orderBy('sort')
            ->get();

        $tree = $this->buildAllCategoriesTree($categories);
        $flat = $this->buildAllCategoriesFlat($tree);

        return Collection::make($flat);
    }

    /**
     * Build parent categories flat
     *
     * @param Model $category
     * @param array $tree
     *
     * @return array
     */
    private function buildParentCategoriesFlat(Model $category, array &$tree = []): array
    {
        if ($category->parent->id) {
            $this->buildParentCategoriesFlat($category->parent, $tree);
        }

        $tree[] = $category;

        return $tree;
    }

    /**
     * Build all categories tree
     *
     * @param Collection $categories
     * @param int        $parentId
     * @param int        $depth
     *
     * @return array
     */
    private function buildAllCategoriesTree(Collection $categories, int $parentId = 0, int $depth = 0): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $child = $this->buildAllCategoriesTree($categories, $category->id, $depth + 1);

                $category->depth = $depth;

                if ($child) {
                    $category->child = $child;
                }

                $tree[] = $category;
            }
        }

        return $tree;
    }

    /**
     * Build all categories flat
     *
     * @param array $categories
     * @param array $flat
     *
     * @return array
     */
    private function buildAllCategoriesFlat(array $categories, array &$flat = []): array
    {
        foreach ($categories as $category) {
            $flat[] = $category;

            if (isset($category->child)) {
                $this->buildAllCategoriesFlat($category->child, $flat);
            }
        }

        return $flat;
    }
}
