<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @throws ReflectionException
     */
    protected function callMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        return (new ReflectionClass($object))
            ->getMethod($methodName)
            ->invokeArgs($object, $parameters);
    }
}
