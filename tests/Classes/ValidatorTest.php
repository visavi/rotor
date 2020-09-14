<?php

namespace Tests\Classes;

use App\Classes\Validator;
use Illuminate\Http\UploadedFile;
use stdClass;

class ValidatorTest extends \Tests\TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    /**
     * Тестирует длину строки
     */
    public function testLength(): void
    {
        $this->validator->length('test', 0, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->length('', 5, 10, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->length('test', 5, 10, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->length('testtesttest', 0, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует интервал чисел
     */
    public function testBetween(): void
    {
        $this->validator->between(15, 10, 20, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->between(30, 10, 20, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует больше ли число
     */
    public function testGt(): void
    {
        $this->validator->gt(15, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->gt(10, 10, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->gt(5, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует равно или больше ли число
     */
    public function testGte(): void
    {
        $this->validator->gte(15, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->gte(10, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->gte(5, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует меньше ли число
     */
    public function testLt(): void
    {
        $this->validator->lt(5, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->lt(10, 10, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->lt(15, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует равно или меньше ли число
     */
    public function testLte(): void
    {
        $this->validator->lte(5, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->lte(10, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->lte(15, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует эквивалентность данных
     */
    public function testEqual(): void
    {
        $this->validator->equal('foo', 'foo', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->equal(5, 5, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->equal('foo', 'bar', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует неэквивалентность данных
     */
    public function testNotEqual(): void
    {
        $this->validator->notEqual('foo', 'bar', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEqual(5, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEqual('foo', 'foo', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует пустоту данных
     */
    public function testEmpty(): void
    {
        $this->validator->empty('', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->empty(0, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->empty(null, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->empty(false, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->empty(true, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->empty('foo', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует непустоту данных
     */
    public function testNotEmpty(): void
    {
        $this->validator->notEmpty('foo', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEmpty(true, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEmpty(5, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEmpty('', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на true
     */
    public function testTrue(): void
    {
        $this->validator->true(true, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true(5 > 3, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true([], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true(new StdClass(), 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true('', 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->true(0, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->true(false, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на false
     */
    public function testFalse(): void
    {
        $this->validator->false(false, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false(5 < 3, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false(0, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false('', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false(5, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->false(true, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на вхождение в массив
     */
    public function testIn(): void
    {
        $this->validator->in('bar', ['foo', 'bar'], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->in(5, [1, 2, 3, 4, 5], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->in(true, [1, 2, 3, 4, 5], 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->in(6, [1, 2, 3, 4, 5], ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на не вхождение в массив
     */
    public function testNotIn(): void
    {
        $this->validator->notIn('test', ['foo', 'bar'], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notIn(6, [1, 2, 3, 4, 5], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notIn(true, [1, 2, 3, 4, 5], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notIn([], [1, 2, 3, 4, 5], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notIn(5, [1, 2, 3, 4, 5], ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует по регулярному выражению
     */
    public function testRegex(): void
    {
        $this->validator->regex('fooBAR', '/^[a-z0-9\-]+$/i', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->regex('11.12.1991', '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->regex('', '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->regex(null, '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->regex('foo%BAR', '/^[a-z0-9\-]+$/i', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на число в плавающей точкой
     */
    public function testFloat(): void
    {
        $this->validator->float(0.0, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->float(1e5, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->float(null, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->float(5, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует проверку адреса сайта
     */
    public function testUrl(): void
    {
        $this->validator->url('http://visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url('https://visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт.рф', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url(null, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт/.рф', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует проверку email адреса
     */
    public function testEmail(): void
    {
        $this->validator->email('admin@visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->email(null, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->email('fob@bar', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Validate IP address
     */
    public function testIp(): void
    {
        $this->validator->ip('127.0.0.1', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->ip(null, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->ip('::1', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->ip('127.0.0.256', 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->ip('fe80::200:5aee:feaa:20a2::', 'error');
        self::assertFalse($this->validator->isValid());
    }

    /**
     * Тестирует добавление ошибки
     */
    public function testAddError(): void
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey(0, $this->validator->getErrors());
        self::assertArrayHasKey('key', $this->validator->getErrors());
        self::assertArrayHasKey('key2', $this->validator->getErrors());
    }

    /**
     * Тестирует очистку сообщений
     */
    public function testClearErrors(): void
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        self::assertTrue($this->validator->isValid());
    }

    /**
     * Тестирует изображение
     */
    public function testFile(): void
    {
        $image  = UploadedFile::fake()->image('avatar.jpg');
        $image2 = UploadedFile::fake()->image('avatar.jpg', 100, 100);
        $image3 = UploadedFile::fake()->image('avatar.tiff');
        $image4 = new StdClass();

        $rules = [
            'maxweight' => 50,
        ];

        $this->validator->file(null, $rules, 'error', false);
        self::assertTrue($this->validator->isValid());


        $this->validator->file($image, $rules, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->file($image2, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->file($image3, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $rules = [
            'maxsize' => 1,
        ];

        $this->validator->clearErrors();
        $this->validator->file($image, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $rules = [
            'minweight' => 50,
        ];

        $this->validator->clearErrors();
        $this->validator->file($image, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->file($image4, $rules, 'error');
        self::assertFalse($this->validator->isValid());
    }
}
