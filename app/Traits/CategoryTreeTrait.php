<?php

declare(strict_types=1);

namespace App\Traits;

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

        $tree = $this->buildAllCategoriesTree($categories);
        $flat = $this->buildAllCategoriesFlat($tree);

        return Collection::make($flat);
    }

    /**
     * Build parent categories flat
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
