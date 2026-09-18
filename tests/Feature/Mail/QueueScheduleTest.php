<?php

namespace Tests\Feature\Mail;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QueueScheduleTest extends TestCase
{
    public function testWorkerIsScheduled(): void
    {
        Artisan::call('schedule:list');

        self::assertStringContainsString(
            'queue:work database --stop-when-empty',
            Artisan::output(),
            'Задача queue:work не зарегистрирована в планировщике',
        );
    }
}
