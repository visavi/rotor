<?php

namespace Tests\Unit;

use App\Models\Antimat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperTest extends TestCase
{
    use RefreshDatabase;

    public function testDateFixed(): void
    {
        self::assertSame('01.06.2005 / 12:00', dateFixed(1117612800));
        self::assertSame('2005-06-01', dateFixed(1117612800, 'Y-m-d'));
        self::assertSame('1 Июня 2005', dateFixed(1117612800, 'j F Y'));
        self::assertSame('1 June 2005', dateFixed(1117612800, 'j F Y', true));
        self::assertSame(dateFixed(time(), 'YmdHi'), dateFixed(null, 'YmdHi'));
    }

    public function testCheck(): void
    {
        self::assertSame('&lt;br&gt;', check('<br>'));
        self::assertSame('&amp;lt;br&amp;gt;', check('&lt;br&gt;'));
        self::assertSame('&lt;br&gt;', check('&lt;br&gt;', false));
        self::assertSame(['&lt;br&gt;', '&lt;html&gt;'], check(['<br>', '<html>']));
        self::assertSame('', check(chr(0) . "\x00" . "\x1A" . chr(226) . chr(128) . chr(174)));
        self::assertSame(' test ', check(' test '));
    }

    public static function intConversionsProvider(): array
    {
        return [
            'positive int'     => [5, 5],
            'string int'       => ['5', 5],
            'false'            => [false, 0],
            'true'             => [true, 1],
            'string with text' => ['1abc', 1],
            'negative int'     => [-1, 1],
            'negative string'  => ['-1', 1],
            'empty array'      => [[], 0],
            'non-empty array'  => [[1], 1],
        ];
    }

    #[DataProvider('intConversionsProvider')]
    public function testInt(mixed $input, int $expected): void
    {
        self::assertSame($expected, int($input));
    }

    public function testIntar(): void
    {
        self::assertSame([5], intar(5));
        self::assertSame([5], intar('5'));
        self::assertSame([0, 1, 33, -1, 0, 0], intar([false, true, '33abc', '-1', 0.01, 'abc']));
        self::assertNull(intar(null));
        self::assertNull(intar(0));
    }

    public static function formatSizeProvider(): array
    {
        return [
            'bytes'     => [5, 2, '5B'],
            'kilobytes' => [1024, 2, '1Kb'],
            'megabytes' => [1048576, 2, '1Mb'],
            'terabytes' => [1099511627776, 2, '1Tb'],
            'precision' => [1000, 4, '0.9766Kb'],
        ];
    }

    #[DataProvider('formatSizeProvider')]
    public function testFormatSize(int $bytes, int $precision, string $expected): void
    {
        self::assertSame($expected, formatSize($bytes, $precision));
    }

    public function testFormatFileSize(): void
    {
        self::assertSame('0B', formatFileSize(UploadedFile::fake()->create('test.txt')->getPathname()));
    }

    public static function formatTimeProvider(): array
    {
        return [
            'zero'   => [0, '0'],
            'minute' => [60, '1 минута'],
            'hour'   => [3600, '1 час'],
            'day'    => [86400, '1 день'],
            'year'   => [86400 * 365, '1 год'],
        ];
    }

    #[DataProvider('formatTimeProvider')]
    public function testFormatTime(int $seconds, string $expected): void
    {
        self::assertSame($expected, formatTime($seconds));
    }

    public function testAntimat(): void
    {
        Antimat::query()->create(['string' => 'xxx']);

        self::assertSame('test', antimat('test'));
        self::assertSame('test***test', antimat('testxxxtest'));
        self::assertSame('тест***тест***', antimat('тестxxxтестxxx'));
    }

    public static function hideMailProvider(): array
    {
        return [
            'long local part' => ['admin@example.com', 'a****@example.com'],
            'two chars'       => ['ab@example.com', 'a*@example.com'],
            'single char'     => ['a@example.com', 'a@example.com'],
            'four chars'      => ['test@test.ru', 't***@test.ru'],
        ];
    }

    #[DataProvider('hideMailProvider')]
    public function testHideMail(string $email, string $expected): void
    {
        self::assertSame($expected, hideMail($email));
    }

    public static function iconsProvider(): array
    {
        return [
            'php'     => ['php', 'fa-regular fa-file-code'],
            'mp3'     => ['mp3', 'fa-regular fa-file-audio'],
            'jpg'     => ['jpg', 'fa-regular fa-file-image'],
            'zip'     => ['zip', 'fa-regular fa-file-archive'],
            'pdf'     => ['pdf', 'fa-regular fa-file-pdf'],
            'unknown' => ['unknown', 'fa-regular fa-file'],
        ];
    }

    #[DataProvider('iconsProvider')]
    public function testIcons(string $ext, string $expectedClass): void
    {
        self::assertStringContainsString($expectedClass, icons($ext)->toHtml());
    }

    public function testTruncateDescription(): void
    {
        self::assertSame('Hello World', truncateDescription('Hello World', 5));
        self::assertSame('Hello', truncateDescription('Hello World', 1, ''));
        self::assertSame('Hello...', truncateDescription('Hello World', 1, '...'));
        self::assertSame('Hello World', truncateDescription("<b>Hello</b>\nWorld", 5));
    }

    public function testFormatNum(): void
    {
        self::assertSame('<span style="color:#00aa00">+5</span>', formatNum(5)->toHtml());
        self::assertSame('<span style="color:#ff0000">-3</span>', formatNum(-3)->toHtml());
        self::assertSame('<span>0</span>', formatNum(0)->toHtml());
    }

    public static function formatShortNumProvider(): array
    {
        return [
            'below threshold' => [999, 999],
            'exact thousand'  => [1000, 1000],
            'kilos'           => [1500, '1.5K'],
            'megas'           => [1500000, '1.5M'],
            'gigas'           => [1500000000, '1.5B'],
            'teras'           => [1500000000000, '1.5T'],
        ];
    }

    #[DataProvider('formatShortNumProvider')]
    public function testFormatShortNum(int $input, int|string $expected): void
    {
        self::assertSame($expected, formatShortNum($input));
    }

    public static function pluralProvider(): array
    {
        return [
            '1 (first form)'   => [1, ['год', 'года', 'лет'], '1 год'],
            '2 (second form)'  => [2, ['год', 'года', 'лет'], '2 года'],
            '5 (third form)'   => [5, ['год', 'года', 'лет'], '5 лет'],
            '11 (exception)'   => [11, ['год', 'года', 'лет'], '11 лет'],
            '21 (first again)' => [21, ['год', 'года', 'лет'], '21 год'],
            '100 (third form)' => [100, ['год', 'года', 'лет'], '100 лет'],
        ];
    }

    #[DataProvider('pluralProvider')]
    public function testPlural(int $num, array $forms, string $expected): void
    {
        self::assertSame($expected, plural($num, $forms));
    }

    public function testPlural_stringForms(): void
    {
        self::assertSame('1 штука', plural(1, 'штука'));
        self::assertSame('5 лет', plural(5, 'год,года,лет'));
    }

    public static function extensionProvider(): array
    {
        return [
            'simple'       => ['photo.jpg', 'jpg'],
            'double ext'   => ['archive.tar.gz', 'gz'],
            'no extension' => ['noextension', ''],
            'uppercase'    => ['script.PHP', 'PHP'],
        ];
    }

    #[DataProvider('extensionProvider')]
    public function testGetExtension(string $filename, string $expected): void
    {
        self::assertSame($expected, getExtension($filename));
    }

    public static function bodyNameProvider(): array
    {
        return [
            'simple'       => ['photo.jpg', 'photo'],
            'double ext'   => ['archive.tar.gz', 'archive.tar'],
            'no extension' => ['noextension', 'noextension'],
        ];
    }

    #[DataProvider('bodyNameProvider')]
    public function testGetBodyName(string $filename, string $expected): void
    {
        self::assertSame($expected, getBodyName($filename));
    }

    public function testClearCache(): void
    {
        self::assertTrue(clearCache());
        self::assertTrue(clearCache('testKey'));
        self::assertTrue(clearCache(['key1', 'key2']));
    }

    public function testTruncateHtml(): void
    {
        self::assertSame('Hello World', truncateHtml('<b>Hello</b> World', 5)->toHtml());
        self::assertSame('Hello...', truncateHtml('Hello World', 1)->toHtml());
        self::assertSame('Hello!', truncateHtml('Hello World', 1, '!')->toHtml());
        self::assertSame('', truncateHtml(null)->toHtml());
    }

    public static function parseVersionProvider(): array
    {
        return [
            'full version'  => ['14.0.0', '14.0.0'],
            'short version' => ['14.0', '14.0.0'],
            'major only'    => ['14', '14.0.0'],
            'with suffix'   => ['14.0.0-beta', '14.0.0'],
            'extra parts'   => ['1.2.3.4', '1.2.3'],
            'empty string'  => ['', '0.0.0'],
        ];
    }

    #[DataProvider('parseVersionProvider')]
    public function testParseVersion(string $version, string $expected): void
    {
        self::assertSame($expected, parseVersion($version));
    }

    public function testUniqueName(): void
    {
        $name = uniqueName();

        self::assertStringNotContainsString('.', $name);
        self::assertNotSame($name, uniqueName());
        self::assertStringEndsWith('.jpg', uniqueName('jpg'));
    }

    public function testAbsolutizeUrls(): void
    {
        config(['app.url' => 'https://site.test']);

        self::assertSame(
            '<a href="https://site.test/page">x</a>',
            absolutizeUrls('<a href="/page">x</a>')
        );
        self::assertSame(
            '<img src="https://site.test/img/a.png">',
            absolutizeUrls('<img src="/img/a.png">')
        );
        self::assertSame(
            '<a href="https://other.test/page">x</a>',
            absolutizeUrls('<a href="https://other.test/page">x</a>')
        );
    }

    public function testDeleteDir(): void
    {
        $dir = sys_get_temp_dir() . '/helper_test_' . uniqid();
        mkdir($dir . '/nested', 0777, true);
        file_put_contents($dir . '/file.txt', 'data');
        file_put_contents($dir . '/nested/inner.txt', 'data');

        deleteDir($dir);

        self::assertDirectoryDoesNotExist($dir);

        deleteDir($dir . '/missing');
    }

    public function testDeleteFile(): void
    {
        $file = sys_get_temp_dir() . '/helper_test_' . uniqid() . '.txt';
        file_put_contents($file, 'data');

        self::assertTrue(deleteFile($file));
        self::assertFileDoesNotExist($file);
        self::assertTrue(deleteFile($file));
    }

    public function testPaginate(): void
    {
        $paginator = paginate([1, 2, 3, 4, 5], 2, ['sort' => 'name']);

        self::assertSame(5, $paginator->total());
        self::assertSame(3, $paginator->lastPage());
        self::assertSame([1, 2], $paginator->items());
        self::assertStringContainsString('sort=name', $paginator->url(2));
    }

    public function testSimplePaginate(): void
    {
        $paginator = simplePaginate([1, 2, 3], 2);

        self::assertTrue($paginator->hasMorePages());
        self::assertCount(2, $paginator->items());
    }
}
