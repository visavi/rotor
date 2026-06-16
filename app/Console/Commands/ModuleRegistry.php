<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ModuleRegistry extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:registry
        {path? : Каталог с модулями или конкретный модуль (по умолчанию base_path(\'modules\'))}
        {--o|output= : Файл для записи (по умолчанию stdout)}
        {--name=Official Rotor Modules : Название реестра}
        {--base-url= : База для download_url, напр. https://example.com/modules}
        {--existing= : Существующий registry.json для накопления версий}';

    /**
     * The console command description.
     */
    protected $description = 'Build a module registry.json from local modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path') ?: base_path('modules');

        if (! is_dir($path)) {
            $this->error("Каталог не найден: {$path}");

            return SymfonyCommand::FAILURE;
        }

        // Накопитель версий: подхватываем существующий реестр, чтобы старые версии не терялись
        $modules = $this->loadExisting();

        // Путь — либо один модуль (есть module.php), либо каталог с модулями
        $dirs = is_file($path . '/module.php') ? [$path] : (array) glob($path . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $manifest = $dir . '/module.php';

            if (! is_file($manifest)) {
                continue;
            }

            $config = include $manifest;

            if (! is_array($config) || empty($config['version'])) {
                continue;
            }

            $name = basename($dir);
            $modules[$name] = $this->buildModule($modules[$name] ?? null, $name, $dir, $config);
        }

        $registry = [
            'name'    => (string) $this->option('name'),
            'modules' => array_values($modules),
        ];

        $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($output = $this->option('output')) {
            file_put_contents($output, $json . "\n");
            $this->info('Реестр записан: ' . $output . ' (' . count($modules) . ' модулей)');
        } else {
            $this->line($json);
        }

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Загружает существующий реестр в map по имени модуля
     *
     * @return array<string, array<string, mixed>>
     */
    private function loadExisting(): array
    {
        $file = $this->option('existing');

        if (! $file || ! is_file($file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($file), true);
        $modules = [];

        foreach ($data['modules'] ?? [] as $module) {
            if (isset($module['module'])) {
                $modules[$module['module']] = $module;
            }
        }

        return $modules;
    }

    /**
     * Собирает запись модуля, добавляя текущую версию к накопленным
     *
     * @param array<string, mixed>|null $existing
     * @param array<string, mixed>      $config
     *
     * @return array<string, mixed>
     */
    private function buildModule(?array $existing, string $name, string $dir, array $config): array
    {
        $version = (string) $config['version'];

        $entry = [
            'version'      => $version,
            'requires'     => (string) ($config['requires'] ?? ''),
            'download_url' => $this->downloadUrl($name, $version),
        ];

        if ($changelog = $this->changelog($dir, $version)) {
            $entry['changelog'] = $changelog;
        }

        // Накопленные версии минус текущая (перезапишем), сортировка по убыванию
        $versions = array_values(array_filter(
            $existing['versions'] ?? [],
            static fn (array $v) => ($v['version'] ?? null) !== $version,
        ));

        $versions[] = $entry;
        usort($versions, static fn ($a, $b) => version_compare($b['version'], $a['version']));

        return [
            'module'      => $name,
            'name'        => (string) ($config['name'] ?? $name),
            'description' => (string) ($config['description'] ?? ''),
            'author'      => (string) ($config['author'] ?? ''),
            'email'       => (string) ($config['email'] ?? ''),
            'homepage'    => (string) ($config['homepage'] ?? ''),
            'versions'    => $versions,
        ];
    }

    /**
     * Строит download_url из --base-url (тег Модуль-версия, ассет Модуль.zip — как CI)
     */
    private function downloadUrl(string $name, string $version): string
    {
        $base = $this->option('base-url');

        if (! $base) {
            return '';
        }

        return rtrim($base, '/') . "/{$name}-{$version}/{$name}.zip";
    }

    /**
     * Достаёт текст секции "## <версия>" из changelog.md модуля
     */
    private function changelog(string $dir, string $version): ?string
    {
        foreach (['changelog.md', 'CHANGELOG.md'] as $filename) {
            $file = $dir . '/' . $filename;

            if (is_file($file)) {
                return $this->parseSection((string) file_get_contents($file), $version);
            }
        }

        return null;
    }

    /**
     * Вырезает текст под заголовком "## <версия>" до следующего заголовка
     */
    private function parseSection(string $content, string $version): ?string
    {
        $quoted = preg_quote($version, '/');
        $pattern = '/^##\s+v?' . $quoted . '\s*$(.*?)(?=^##\s|\z)/ms';

        if (preg_match($pattern, $content, $m)) {
            $section = trim($m[1]);

            return $section !== '' ? $section : null;
        }

        return null;
    }
}
