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

    private function node(int $id, int $parentId): Model
    {
        $model = new class extends Model {
        };
        $model->id = $id;
        $model->parent_id = $parentId;

        return $model;
    }
}
