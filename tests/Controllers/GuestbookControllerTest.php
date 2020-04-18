<?php

namespace Tests\Controllers;

use App\Models\Guestbook;

class GuestbookControllerTest extends \Tests\TestCase
{
    public function testGuest(): void
    {
        $guest = Guestbook::query()->create([
            'user_id'    => 1,
            'text'       => 'Test text message',
            'ip'         => '127.0.0.1',
            'brow'       => 'Chrome 60.0',
            'created_at' => SITETIME,
        ]);

        /** @var Guestbook $getGuest */
        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertEquals('Test text message', $getGuest->text);

        $guest->update(['text' => 'Test simple message']);

        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertEquals('Test simple message', $getGuest->text);

        $guest->delete();

        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertNull($getGuest);
    }
}
