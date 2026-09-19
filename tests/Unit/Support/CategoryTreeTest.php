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

        self::assertSame([1, 4], $roots->pluck('id')->all());
        self::assertSame([2], $roots[0]->children->pluck('id')->all());
        self::assertSame([3], $roots[0]->children[0]->children->pluck('id')->all());
        self::assertTrue($roots[1]->children->isEmpty());
    }

    private function node(int $id, int $parentId, int $count = 0): Model
    {
        $model = new class extends Model {
        };
        $model->id = $id;
        $model->parent_id = $parentId;
        $model->count = $count;

        return $model;
    }
}
