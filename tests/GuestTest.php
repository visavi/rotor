<?php

use PHPUnit\Framework\TestCase;
use App\Models\Guest;

class GuestControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->assertTrue(true);
    }

    public function testGuest()
    {
        $guest = new Guest();
        $guest->user_id = 1;
        $guest->text = 'Test text message';
        $guest->ip = '127.0.0.1';
        $guest->brow = 'Chrome 60.0';
        $guest->created_at = SITETIME;
        $guest->save();

        $this->assertTrue($guest->save());

        $getGuest = Guest::query()->find($guest->id);
        $this->assertEquals($getGuest->text, 'Test text message');

        $guest->update(['text' => 'Test simple message']);

        $getGuest = Guest::query()->find($guest->id);
        $this->assertEquals($getGuest->text, 'Test simple message');

        $guest->delete();

        $getGuest = Guest::query()->find($guest->id);
        $this->assertNull($getGuest);
    }
}
