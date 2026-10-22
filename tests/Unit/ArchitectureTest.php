<?php

namespace Tests\Unit;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Правила, которые проще проверить, чем помнить: нарушение валит тест с объяснением
 */
class ArchitectureTest extends TestCase
{
    public function testTestsDoNotTruncate(): void
    {
        $this->assertCodeMissing(
            '/->truncate\(/',
            ['tests', 'modules/*/Tests'],
            'truncate() — это DDL: неявный COMMIT переживает откат RefreshDatabase, записи протекают в соседние тесты. Используйте delete()',
        );
    }

    public function testNoPartialUpsert(): void
    {
        $this->assertCodeMissing(
            '/->upsert\(/',
            ['app', 'modules'],
            'upsert() — это INSERT ... ON DUPLICATE KEY: строгий MySQL требует все NOT NULL-поля даже для существующих строк. Массовое обновление — update() с CASE',
        );
    }

    public function testNoSitetime(): void
    {
        $this->assertCodeMissing(
            '/\bSITETIME\b/',
            ['app', 'modules', 'resources/views'],
            'SITETIME — мост для старых модулей до 15.0. Даты — datetime-колонки и now()',
            ['app/helpers.php'],
        );
    }

    public function testMailLinksAreAbsolute(): void
    {
        $this->assertCodeMissing(
            '/\basset\(/',
            ['resources/views/mailer', 'modules/*/resources/views/mailer'],
            'asset() при ASSET_URL=/ даёт относительный путь — в письме он не откроется. Ссылки наружу — url() или route()',
        );
    }

    /**
     * @param list<string> $paths  пути от корня проекта, можно с * для модулей
     * @param list<string> $except файлы, где запрет не действует
     */
    private function assertCodeMissing(string $pattern, array $paths, string $reason, array $except = []): void
    {
        $dirs = array_filter(
            array_merge(...array_map(static fn (string $path): array => glob(base_path($path), GLOB_ONLYDIR) ?: [], $paths)),
        );

        if (! $dirs) {
            $this->markTestSkipped('Нет каталогов для проверки');
        }

        $found = [];

        $files = Finder::create()->files()->name('*.php')->in($dirs)->followLinks()
            ->notPath(['vendor', 'Docs/resources']);

        foreach ($files as $file) {
            $path = str_replace(base_path() . '/', '', $file->getPathname());

            if (in_array($path, $except, true)) {
                continue;
            }

            foreach (file($file->getPathname()) as $number => $line) {
                // Комментарии объясняют запреты и сами упоминают запрещённое
                if (preg_match('~^\s*(//|\*|/\*)~', $line)) {
                    continue;
                }

                if (preg_match($pattern, $line)) {
                    $found[] = $path . ':' . ($number + 1);
                }
            }
        }

        $this->assertSame([], $found, $reason);
    }
}
