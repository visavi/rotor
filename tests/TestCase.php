<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Call protected/private method of a class.
     *
     * @throws ReflectionException
     */
    public function callMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass(get_class($object));

        return $reflection->getMethod($methodName)->invokeArgs($object, $parameters);
    }
}
