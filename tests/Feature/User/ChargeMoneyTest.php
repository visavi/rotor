<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ChargeMoneyTest extends TestCase
{
    use RefreshDatabase;

    public function testChargeKeepsModelInSync(): void
    {
        $user = User::factory()->create(['money' => 100]);

        $this->assertTrue($user->chargeMoney(30));
        $this->assertSame(70, $user->money);
        $this->assertFalse($user->isDirty('money'));
        $this->assertSame(70, $user->fresh()->money);
    }

    public function testFreeChargeSucceeds(): void
    {
        $user = User::factory()->create(['money' => 0]);

        // Цена 0 разрешена настройками лотереи и подарков: списание ничего не меняет, но проходит
        $this->assertTrue($user->chargeMoney(0));
        $this->assertSame(0, $user->fresh()->money);
    }

    public function testNegativeChargeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::factory()->create(['money' => 0])->chargeMoney(-10);
    }

    public function testUnsavedUserChangesNobody(): void
    {
        // Связь withDefault отдаёт пустую модель удалённого автора: increment на ней
        // у Eloquent обновляет всю таблицу, награда доставалась всем пользователям
        $other = User::factory()->create(['money' => 100, 'point' => 10]);

        (new User())->increment('money', 500);
        (new User())->decrement('point', 5);
        (new User())->incrementEach(['money' => 1, 'point' => 1]);

        $this->assertSame(100, $other->fresh()->money);
        $this->assertSame(10, $other->fresh()->point);
    }

    public function testStaleBalanceDoesNotGoNegative(): void
    {
        $user = User::factory()->create(['money' => 100]);

        // Модель прочитала баланс, а параллельный запрос уже его потратил
        User::query()->whereKey($user->id)->update(['money' => 10]);

        $this->assertFalse($user->chargeMoney(50));
        $this->assertSame(10, $user->fresh()->money);

        $this->assertTrue($user->chargeMoney(10));
        $this->assertSame(0, $user->fresh()->money);
    }
}
