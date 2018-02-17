<?php

use App\Classes\Validator;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
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

        $this->validator->length('test', 5, 10, 'error');
        $this->assertFalse($this->validator->isValid());

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
     * Тестирует на вхождение в массив
     */
    public function testIn()
    {
        $this->validator->in('bar', ['foo', 'bar'], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->in(5, [1, 2, 3, 4, 5], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->in(true, [1, 2, 3, 4, 5], 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        $this->validator->in(6, [1, 2, 3, 4, 5], ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на вхождение в массив
     */
    public function testNotIn()
    {
        $this->validator->notIn('test', ['foo', 'bar'], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notIn(6, [1, 2, 3, 4, 5], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notIn(true, [1, 2, 3, 4, 5], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notIn([], [1, 2, 3, 4, 5], 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->notIn(5, [1, 2, 3, 4, 5], ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует по регулярному выражению
     */
    public function testRegex()
    {
        $this->validator->regex('fooBAR', '/^[a-z0-9\-]+$/i', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->regex('11.12.1991', '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->regex('', '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->regex(null, '/^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$/', 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->regex('foo%BAR', '/^[a-z0-9\-]+$/i', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует на число в плавающей точкой
     */
    public function testFloat()
    {
        $this->validator->float(0.0, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->float(1e5, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->float(null, 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->float(5, ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует проверку адреса сайта
     */
    public function testUrl()
    {
        $this->validator->url('http://visavi.net', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->url('https://visavi.net', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт.рф', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->url(null, 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт/.рф', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует проверку email адреса
     */
    public function testEmail()
    {
        $this->validator->email('admin@visavi.net', 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->email(null, 'error', false);
        $this->assertTrue($this->validator->isValid());

        $this->validator->email('fob@bar', ['key' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey('key', $this->validator->getErrors());
    }

    /**
     * Тестирует добавление ошибки
     */
    public function testAddError()
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);
        $this->assertFalse($this->validator->isValid());
        $this->assertArrayHasKey(0, $this->validator->getErrors());
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

    /**
     * Тестирует изображение
     */
    public function testImage()
    {
        $image = UploadedFile::fake()->image('avatar.jpg');
        $image2 = UploadedFile::fake()->image('avatar.jpg', 100, 100);
        $image3 = UploadedFile::fake()->image('avatar.tiff');

        $rules = [
            'maxweight' => 50,
        ];

        $this->validator->image(null, $rules, 'error', false);
        $this->assertTrue($this->validator->isValid());


        $this->validator->image($image, $rules, 'error');
        $this->assertTrue($this->validator->isValid());

        $this->validator->image($image2, $rules, 'error');
        $this->assertFalse($this->validator->isValid());

        $this->validator->image($image3, $rules, 'error');
        $this->assertFalse($this->validator->isValid());

        $rules = [
            'maxsize' => 1,
        ];

        $this->validator->image($image, $rules, 'error');
        $this->assertFalse($this->validator->isValid());

        $rules = [
            'minsize' => 1000,
        ];

        $this->validator->image($image, $rules, 'error');
        $this->assertFalse($this->validator->isValid());

        $rules = [
            'minweight' =>1333333,
        ];

        $this->validator->image($image, $rules, 'error');
        var_dump($this->validator->isValid());
        $this->assertFalse($this->validator->isValid());
    }
}
