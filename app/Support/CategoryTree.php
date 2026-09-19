<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CategoryTree
{
    /**
     * Раскладывает дерево категорий в плоский список с проставленной глубиной
     *
     * Потерянные ветки (родителя нет в наборе) в результат не попадают
     *
     * @template TModel of Model
     *
     * @param Collection<int, TModel> $categories
     *
     * @return Collection<int, TModel>
     */
    public static function flatten(Collection $categories): Collection
    {
        $children = [];

        foreach ($categories as $category) {
            $children[(int) $category->getAttribute('parent_id')][] = $category;
        }

        return new Collection(self::walk($children, 0, 0));
    }

    /**
     * Обходит уровень дерева сверху вниз
     *
     * @template TModel of Model
     *
     * @param array<int, array<int, TModel>> $children
     *
     * @return array<int, TModel>
     */
    private static function walk(array $children, int $parentId, int $depth): array
    {
        $flat = [];

        foreach ($children[$parentId] ?? [] as $category) {
            $category->setAttribute('depth', $depth);
            $flat[] = $category;

            array_push($flat, ...self::walk($children, (int) $category->getAttribute('id'), $depth + 1));
        }

        return $flat;
    }

    /**
     * Расставляет связь children по загруженному набору и возвращает корни
     *
     * Списки разделов показывают дерево целиком, и подгрузка детей на каждый
     * узел означала бы запрос на строку. Набор приходит одним запросом, связи
     * раздаются из памяти
     *
     * @template TModel of Model
     *
     * @param Collection<int, TModel> $categories
     *
     * @return Collection<int, TModel>
     */
    public static function nest(Collection $categories): Collection
    {
        $children = [];

        foreach ($categories as $category) {
            $children[(int) $category->getAttribute('parent_id')][] = $category;
        }

        foreach ($categories as $category) {
            $id = (int) $category->getAttribute('id');

            $category->setRelation('children', new Collection($children[$id] ?? []));
        }

        return new Collection($children[0] ?? []);
    }

    /**
     * Проставляет каждому узлу суммы счётчиков по всему его поддереву
     *
     * Считается по уже загруженному набору, без запроса на узел. Раньше список
     * складывал счётчик раздела с суммой прямых детей, и внуки терялись
     *
     * @template TModel of Model
     *
     * @param Collection<int, TModel> $categories
     * @param array<string, string>   $fields     поле счётчика => атрибут с суммой
     */
    public static function totals(Collection $categories, array $fields): void
    {
        $parents = [];

        foreach ($categories as $category) {
            $parents[(int) $category->getAttribute('id')] = (int) $category->getAttribute('parent_id');
        }

        $limit = count($parents);

        foreach ($fields as $field => $attribute) {
            $sums = [];

            foreach ($categories as $category) {
                $value = (int) $category->getAttribute($field);
                $id = (int) $category->getAttribute('id');

                // Значение узла поднимается по цепочке предков. Шаги ограничены
                // размером набора: кольцо в данных иначе крутило бы цикл вечно
                for ($step = 0; $id !== 0 && $step <= $limit; $step++) {
                    $sums[$id] = ($sums[$id] ?? 0) + $value;
                    $id = $parents[$id] ?? 0;
                }
            }

            foreach ($categories as $category) {
                $category->setAttribute($attribute, $sums[(int) $category->getAttribute('id')] ?? 0);
            }
        }
    }

    /**
     * Сохраняет порядок и вложенность категорий
     *
     * Строка приходит от JS в виде id:parent_id через запятую, в порядке обхода
     * дерева. Некорректные пары отбрасываются молча: строку руками не набирают,
     * а частичное применение хуже, чем игнор мусора
     *
     * @param class-string<Model> $model
     */
    public static function reorder(string $model, string $order): void
    {
        // Текущее состояние всех записей: ключи — множество существующих id,
        // значения — родители. Проверять цикл по одним лишь присланным парам
        // нельзя: частичная строка легко замкнёт кольцо через звено из базы
        $current = [];

        foreach ($model::query()->pluck('parent_id', 'id') as $id => $parentId) {
            $current[(int) $id] = (int) $parentId;
        }

        $parents = [];

        foreach (explode(',', $order) as $pair) {
            [$id, $parentId] = array_pad(explode(':', $pair, 2), 2, null);

            $id = (int) $id;
            $parentId = (int) $parentId;

            if (! isset($current[$id])) {
                continue;
            }

            if ($parentId !== 0 && ! isset($current[$parentId])) {
                continue;
            }

            $parents[$id] = $parentId;
        }

        // Присланные пары накладываются поверх состояния базы — цикл ищется
        // по тому дереву, которое получится после записи
        // Именно объединение массивов: спред у целочисленных ключей их перенумеровал бы
        $merged = $parents + $current;

        $parents = array_filter(
            $parents,
            static fn (int $id) => ! self::hasCycle($id, $merged),
            ARRAY_FILTER_USE_KEY,
        );

        if (! $parents) {
            return;
        }

        $sort = 0;

        $rows = [];

        foreach ($parents as $id => $parentId) {
            $rows[] = ['id' => $id, 'parent_id' => $parentId, 'sort' => ++$sort];
        }

        // Одной командой, а не апдейтом на раздел: строка порядка приходит целиком,
        // и на полусотне разделов это была полусотня запросов за нажатие кнопки
        $model::query()->upsert($rows, ['id'], ['parent_id', 'sort']);
    }

    /**
     * Проверяет, не замыкается ли цепочка родителей на себя
     *
     * SortableJS не даёт уронить узел в собственное поддерево, но строка
     * приходит от клиента, и доверия ей нет
     *
     * Карта должна быть полной (состояние базы плюс присланные пары), иначе
     * подъём упрётся в звено, которого в карте нет, и цикл останется незамеченным
     *
     * @param array<int, int> $parents Карта «узел → родитель» по всем записям
     */
    private static function hasCycle(int $id, array $parents): bool
    {
        $current = $parents[$id] ?? 0;

        for ($step = 0, $limit = count($parents); $step <= $limit; $step++) {
            if ($current === 0) {
                return false;
            }

            if ($current === $id) {
                return true;
            }

            $current = $parents[$current] ?? 0;
        }

        return true;
    }
}
