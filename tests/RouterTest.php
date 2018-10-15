<?php

use Curl\Curl;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testRouter(): void
    {
        $curl = new Curl();
        $curl->get(env('SITE_URL') . '/guestbooks');

        $this->assertEquals(200, $curl->getHttpStatusCode());

    }
}
