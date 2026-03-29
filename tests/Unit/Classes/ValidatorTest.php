<?php

namespace Tests\Unit\Classes;

use App\Classes\Validator;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Validator::class)]
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testLength(): void
    {
        $this->validator->length('test', 0, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->length('', 5, 10, 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->length('test', 5, 10, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->length('testtesttest', 0, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testBetween(): void
    {
        $this->validator->between(15, 10, 20, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->between(30, 10, 20, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testGt(): void
    {
        $this->validator->gt(15, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->gt(10, 10, 'error');
        self::assertFalse($this->validator->isValid());
    }

    public function testGte(): void
    {
        $this->validator->gte(10, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->gte(5, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testLt(): void
    {
        $this->validator->lt(5, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->lt(10, 10, 'error');
        self::assertFalse($this->validator->isValid());
    }

    public function testLte(): void
    {
        $this->validator->lte(10, 10, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->lte(15, 10, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testEqual(): void
    {
        $this->validator->equal('foo', 'foo', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->equal('foo', 'bar', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testNotEqual(): void
    {
        $this->validator->notEqual('foo', 'bar', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEqual('foo', 'foo', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

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
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testNotEmpty(): void
    {
        $this->validator->notEmpty('foo', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notEmpty('', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testTrue(): void
    {
        $this->validator->true(true, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true(5 > 3, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->true(false, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testFalse(): void
    {
        $this->validator->false(false, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false(5 < 3, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->false(true, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testIn(): void
    {
        $this->validator->in('bar', ['foo', 'bar'], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->in(true, [1, 2, 3, 4, 5], 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->in(6, [1, 2, 3, 4, 5], ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testNotIn(): void
    {
        $this->validator->notIn('test', ['foo', 'bar'], 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->notIn(5, [1, 2, 3, 4, 5], ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testRegex(): void
    {
        $this->validator->regex('fooBAR', '/^[a-z0-9\-]+$/i', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->regex('', '/^[0-9]+$/', 'error', false);
        self::assertTrue($this->validator->isValid());

        $this->validator->regex('foo%BAR', '/^[a-z0-9\-]+$/i', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testFloat(): void
    {
        $this->validator->float(0.0, 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->float(5, ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testUrl(): void
    {
        $this->validator->url('http://visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url('https://visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт.рф', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->url('http://сайт/.рф', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testEmail(): void
    {
        $this->validator->email('admin@visavi.net', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->email('fob@bar', ['key' => 'error']);
        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey('key', $this->validator->getErrors());
    }

    public function testIp(): void
    {
        $this->validator->ip('127.0.0.1', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->ip('::1', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->ip('127.0.0.256', 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->ip('fe80::200:5aee:feaa:20a2::', 'error');
        self::assertFalse($this->validator->isValid());
    }

    public function testPhone(): void
    {
        $this->validator->phone('89001234567', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->phone('8900123456789', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->phone('+79001234567', 'error');
        self::assertTrue($this->validator->isValid());

        $this->validator->phone('89001234567890', 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();
        $this->validator->phone('1234567', 'error');
        self::assertFalse($this->validator->isValid());
    }

    public function testAddErrorAndClearErrors(): void
    {
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key' => 'error']);
        $this->validator->addError(['key2' => 'error']);

        self::assertFalse($this->validator->isValid());
        self::assertArrayHasKey(0, $this->validator->getErrors());
        self::assertArrayHasKey('key', $this->validator->getErrors());
        self::assertArrayHasKey('key2', $this->validator->getErrors());

        $this->validator->clearErrors();
        self::assertTrue($this->validator->isValid());
    }

    public function testFile(): void
    {
        // valid image
        $image = UploadedFile::fake()->image('avatar.jpg');
        $rules = ['extensions' => ['jpg', 'jpeg', 'gif', 'png', 'webp'], 'maxweight' => 50];
        $this->validator->file($image, $rules, 'error');
        self::assertTrue($this->validator->isValid());

        // optional null
        $this->validator->file(null, ['extensions' => ['jpg'], 'maxweight' => 50], 'error', false);
        self::assertTrue($this->validator->isValid());

        // too large (maxweight)
        $image = UploadedFile::fake()->image('avatar.jpg', 100, 100);
        $this->validator->file($image, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        // wrong extension
        $image = UploadedFile::fake()->image('avatar.tiff');
        $this->validator->file($image, $rules, 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        // exceeds maxsize
        $image = UploadedFile::fake()->image('avatar.jpg');
        $this->validator->file($image, ['extensions' => ['jpg', 'jpeg', 'gif', 'png', 'webp'], 'maxsize' => 1], 'error');
        self::assertFalse($this->validator->isValid());

        $this->validator->clearErrors();

        // below minweight
        $image = UploadedFile::fake()->image('avatar.jpg');
        $this->validator->file($image, ['extensions' => ['jpg', 'jpeg', 'gif', 'png', 'webp'], 'minweight' => 50], 'error');
        self::assertFalse($this->validator->isValid());
    }
}
