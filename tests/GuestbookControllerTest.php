<?php

use App\Models\Guestbook;
use PHPUnit\Framework\TestCase;

class GuestbookControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGuest(): void
    {
        /** @var Guestbook|\Illuminate\Database\Eloquent\Model $guest */
        $guest = new Guestbook();
        $guest->user_id = 1;
        $guest->text = 'Test text message';
        $guest->ip = '127.0.0.1';
        $guest->brow = 'Chrome 60.0';
        $guest->created_at = time();
        $guest->save();

        $this->assertTrue($guest->save());

        /** @var Guestbook $getGuest */
        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertEquals($getGuest->text, 'Test text message');

        $guest->update(['text' => 'Test simple message']);

        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertEquals($getGuest->text, 'Test simple message');

        $guest->delete();

        $getGuest = Guestbook::query()->find($guest->id);
        $this->assertNull($getGuest);
    }
}
