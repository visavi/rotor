<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\CategoryTree;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Родитель раздела в дереве категорий (форум, блоги, загрузки, объявления)
 *
 * 0 — корень. Иначе родитель должен существовать и не входить в ветку самого
 * раздела: раздел внутри собственного подраздела замыкает кольцо, пропадает
 * из дерева, а хлебные крошки его страницы уходят в бесконечный подъём
 */
readonly class CategoryParentRule implements ValidationRule
{
    /**
     * @param class-string<Model> $model
     * @param int                 $id      Правимый раздел, 0 — создание нового
     * @param string              $message Ключ перевода ошибки
     */
    public function __construct(
        private string $model,
        private int $id,
        private string $message,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentId = is_numeric($value) ? (int) $value : -1;

        if ($parentId === 0) {
            return;
        }

        $parents = [];

        foreach ($this->model::query()->pluck('parent_id', 'id') as $id => $parent) {
            $parents[(int) $id] = (int) $parent;
        }

        if (! isset($parents[$parentId])) {
            $fail(__($this->message));

            return;
        }

        // Тот же поиск кольца, что у перетаскивания: дерево после записи, подъём от раздела
        if ($this->id && CategoryTree::hasCycle($this->id, [$this->id => $parentId] + $parents)) {
            $fail(__($this->message));
        }
    }
}
