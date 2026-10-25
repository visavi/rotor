<?php

namespace Tests\Feature\Console;

use App\Models\Dialogue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeleteDialoguesTest extends TestCase
{
    use RefreshDatabase;

    public function testOldSystemDialoguesAreDeleted(): void
    {
        $user = User::factory()->create();
        $first = $user->sendMessage($user, 'Первое уведомление', false);
        $second = $user->sendMessage($user, 'Второе уведомление', false);
        $fresh = $user->sendMessage($user, 'Свежее уведомление', false);

        Dialogue::query()
            ->whereIn('message_id', [$first->id, $second->id])
            ->update(['author_id' => 0, 'created_at' => now()->subYears(4)]);
        Dialogue::query()->where('message_id', $fresh->id)->update(['author_id' => 0]);

        $this->assertSame(0, Artisan::call('delete:dialogues'));

        $this->assertDatabaseMissing('messages', ['id' => $first->id]);
        $this->assertDatabaseMissing('messages', ['id' => $second->id]);
        $this->assertDatabaseHas('dialogues', ['message_id' => $fresh->id]);
    }
}
