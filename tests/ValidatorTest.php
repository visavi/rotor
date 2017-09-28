<?php

use App\Classes\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private $validator;

    public function setUp()
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    /**
     * Тестирует длину строки
     */
    public function testLength()
    {
        $this->validator->length('test', 0, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->length('', 5, 10, 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->length('testtesttest', 0, 10, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует интервал чисел
     */
    public function testBetween()
    {
        $this->validator->between(15, 10, 20, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->between(0, 10, 20, 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->between(30, 10, 20, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует больше ли число
     */
    public function testGt()
    {
        $this->validator->gt(15, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->gt(10, 10, 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->gt(5, 10, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует равно или больше ли число
     */
    public function testGte()
    {
        $this->validator->gte(15, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->gte(10, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->gte(5, 10, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует меньше ли число
     */
    public function testLt()
    {
        $this->validator->lt(5, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->lt(10, 10, 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->lt(15, 10, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует равно или меньше ли число
     */
    public function testLte()
    {
        $this->validator->lte(5, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->lte(10, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->lte(15, 10, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует эквивалентность данных
     */
    public function testEqual()
    {
        $this->validator->equal('foo', 'foo', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->equal(5, 5, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->equal('foo', 'bar', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует неэквивалентность данных
     */
    public function testNotEqual()
    {
        $this->validator->notEqual('foo', 'bar', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notEqual(5, 10, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notEqual('foo', 'foo', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует пустоту данных
     */
    public function testEmpty()
    {
        $this->validator->empty('', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->empty(0, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->empty(null, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->empty(false, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->empty(true, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->empty('foo', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует непустоту данных
     */
    public function testNotEmpty()
    {
        $this->validator->notEmpty('foo', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notEmpty(true, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notEmpty(5, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notEmpty('', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на true
     */
    public function testTrue()
    {
        $this->validator->true(true, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->true(5 > 3, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->true([], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->true(new StdClass(), 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->true('', 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->true(0, 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->true(false, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на false
     */
    public function testFalse()
    {
        $this->validator->false(false, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->false(5 < 3, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->false(0, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->false('', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->false(5, 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->false(true, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует добавление ошибки
     */
    public function testAddError()
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
        $this->assertArrayHasKey('key2', $this->validator->getErrors());
    }

    /**
     * Тестирует очистку сообщений
     */
    public function testClearErrors()
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->assertTrue($this->validator->isValid());
    }
}
