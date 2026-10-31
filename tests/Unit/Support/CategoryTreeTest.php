<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\CategoryTree;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CategoryTree::class)]
class CategoryTreeTest extends TestCase
{
    public function testFlattenOrdersTreeAndSetsDepth(): void
    {
        $categories = new Collection([
            $this->node(1, 0),
            $this->node(2, 0),
            $this->node(3, 1),
            $this->node(4, 3),
        ]);

        $flat = CategoryTree::flatten($categories);

        self::assertSame([1, 3, 4, 2], $flat->pluck('id')->all());
        self::assertSame([0, 1, 2, 0], $flat->pluck('depth')->all());
    }

    public function testFlattenDropsOrphans(): void
    {
        $categories = new Collection([
            $this->node(1, 0),
            $this->node(2, 99),
        ]);

        $flat = CategoryTree::flatten($categories);

        self::assertSame([1], $flat->pluck('id')->all());
    }

    public function testTotalsSumWholeSubtree(): void
    {
        $categories = new Collection([
            $this->node(1, 0, 1),
            $this->node(2, 1, 10),
            $this->node(3, 2, 100),
            $this->node(4, 0, 5),
        ]);

        CategoryTree::totals($categories, ['count' => 'total']);

        self::assertSame([111, 110, 100, 5], $categories->pluck('total')->all());
    }

    public function testTotalsIgnoreOrphanedBranch(): void
    {
        $categories = new Collection([
            $this->node(1, 0, 1),
            $this->node(2, 99, 10),
        ]);

        CategoryTree::totals($categories, ['count' => 'total']);

        // Родителя 99 в наборе нет, подъём обрывается на самом узле
        self::assertSame([1, 10], $categories->pluck('total')->all());
    }

    public function testNestSetsChildrenAndReturnsRoots(): void
    {
        $categories = new Collection([
            $this->node(1, 0),
            $this->node(2, 1),
            $this->node(3, 2),
            $this->node(4, 0),
        ]);

        $roots = CategoryTree::nest($categories);
        [$first, $second] = $roots->all();

        self::assertSame([1, 4], $roots->pluck('id')->all());
        [$child] = $this->children($first)->all();

        self::assertSame([2], $this->children($first)->pluck('id')->all());
        self::assertSame([3], $this->children($child)->pluck('id')->all());
        self::assertTrue($this->children($second)->isEmpty());
    }

    public function testNestSetsParentFromSameSet(): void
    {
        // Хлебные крошки идут вверх по parent: без связи — запрос на каждого предка
        $categories = new Collection([
            $this->node(1, 0),
            $this->node(2, 1),
            $this->node(3, 2),
        ]);

        CategoryTree::nest($categories);
        [$root, $child, $grandchild] = $categories->all();

        self::assertSame($child, $grandchild->getRelation('parent'));
        self::assertSame($root, $child->getRelation('parent'));
        // У корня, как у withDefault(), — пустая модель без id
        self::assertNull($root->getRelation('parent')->getKey());
    }

    public function testBranchIdsCollectNodeAndAllDescendants(): void
    {
        $categories = new Collection([
            $this->node(1, 0),
            $this->node(2, 1),
            $this->node(3, 2),
            $this->node(4, 1),
            $this->node(5, 0),
        ]);

        $branch = CategoryTree::branchIds($categories, 1);
        sort($branch);

        self::assertSame([1, 2, 3, 4], $branch);
        self::assertSame([5], CategoryTree::branchIds($categories, 5));
    }

    public function testBranchIdsStopOnCycle(): void
    {
        // Кольцо, уже записанное в базу, не должно вешать обход
        $categories = new Collection([
            $this->node(1, 2),
            $this->node(2, 1),
        ]);

        $branch = CategoryTree::branchIds($categories, 1);
        sort($branch);

        self::assertSame([1, 2], $branch);
    }

    /**
     * @return Collection<int, Model>
     */
    private function children(Model $node): Collection
    {
        $children = $node->getRelation('children');
        self::assertInstanceOf(Collection::class, $children);

        return $children;
    }

    private function node(int $id, int $parentId, int $count = 0): Model
    {
        $model = new class extends Model {
        };
        $model->setAttribute('id', $id);
        $model->setAttribute('parent_id', $parentId);
        $model->setAttribute('count', $count);

        return $model;
    }
}
