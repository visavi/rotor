<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! setting('app_installed')) {
            self::markTestSkipped(
                'App not installed'
            );
        }
    }

    /**
     * A basic test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
