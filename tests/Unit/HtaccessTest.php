<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Запреты по имени файла в корневом .htaccess.
 * Когда корень сайта — каталог движка, а не public, они действуют и на файлы в public
 */
class HtaccessTest extends TestCase
{
    public static function deniedProvider(): array
    {
        return [
            ['.env'],
            ['.git'],
            ['artisan'],
            ['cron.php'],
        ];
    }

    public static function allowedProvider(): array
    {
        return [
            ['index.php'],
            ['test_cron.php'],
            ['cron.php.bak.jpg'],
            ['artisan.png'],
            ['greenville.jpg'],
            ['dotenv.js'],
        ];
    }

    #[DataProvider('deniedProvider')]
    public function testFileIsDenied(string $name): void
    {
        $this->assertTrue($this->isDenied($name));
    }

    #[DataProvider('allowedProvider')]
    public function testFileIsAllowed(string $name): void
    {
        $this->assertFalse($this->isDenied($name));
    }

    /**
     * Совпадает ли имя с одним из запретов <FilesMatch> или <Files ~>
     */
    private function isDenied(string $name): bool
    {
        $content = (string) file_get_contents(dirname(__DIR__, 2) . '/.htaccess');

        preg_match_all('/<(?:FilesMatch|Files\s+~)\s+"([^"]+)">\s*(.*?)<\/Files(?:Match)?>/s', $content, $matches, PREG_SET_ORDER);
        $this->assertNotEmpty($matches);

        foreach ($matches as [, $pattern, $body]) {
            if (str_contains($body, 'Deny from all') && preg_match('#' . str_replace('#', '\#', $pattern) . '#', $name)) {
                return true;
            }
        }

        return false;
    }
}
