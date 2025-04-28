<?php

namespace Tests\Unit\Controllers;

use App\Models\Guestbook;

class GuestbookControllerTest extends \Tests\TestCase
{
    public function testGuest(): void
    {
        Guestbook::unsetEventDispatcher();

        $guest = Guestbook::query()->create([
            'user_id'    => 1,
            'text'       => 'Test text message',
            'ip'         => '127.0.0.1',
            'brow'       => 'Chrome 60.0',
            'created_at' => SITETIME,
        ]);

        /** @var Guestbook $getGuest */
        $getGuest = Guestbook::query()->find($guest->id);
        self::assertEquals('Test text message', $getGuest->text);

        $guest->update(['text' => 'Test simple message']);

        $getGuest = Guestbook::query()->find($guest->id);
        self::assertEquals('Test simple message', $getGuest->text);

        $guest->delete();

        $getGuest = Guestbook::query()->find($guest->id);
        self::assertNull($getGuest);
    }
}
