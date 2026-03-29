<?php

namespace Tests\Feature;

use App\Models\Guestbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Guestbook::class)]
class GuestbookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestbook(): void
    {
        Guestbook::unsetEventDispatcher();

        $guest = Guestbook::query()->create([
            'user_id'    => 1,
            'text'       => 'Test text message',
            'ip'         => '127.0.0.1',
            'brow'       => 'Chrome 60.0',
            'created_at' => SITETIME,
        ]);

        $getGuest = Guestbook::query()->find($guest->id);
        self::assertSame('Test text message', $getGuest->text);

        $guest->update(['text' => 'Test simple message']);

        $getGuest = Guestbook::query()->find($guest->id);
        self::assertSame('Test simple message', $getGuest->text);

        $guest->delete();

        $getGuest = Guestbook::query()->find($guest->id);
        self::assertNull($getGuest);
    }
}
