<?php

namespace Tests\Feature;

use App\Models\Flood;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FloodTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveStateOnLongPath(): void
    {
        // Стена пользователя с логином в 20 символов — путь длиннее прежних 30 символов колонки
        $path = '/walls/' . str_repeat('a', 20) . '/create';
        $this->app->instance('request', Request::create($path, 'POST'));

        $flood = new Flood();
        $flood->saveState(60);

        $this->assertTrue($flood->isFlood());
    }
}
