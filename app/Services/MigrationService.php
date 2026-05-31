<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Console\Output\BufferedOutput;

class MigrationService
{
    public function getPendingMigrations(array $paths): array
    {
        $migrator = app('migrator');

        if (! $migrator->repositoryExists()) {
            return [];
        }

        $files = $migrator->getMigrationFiles($paths);
        $ran = $migrator->getRepository()->getRan();

        return array_values(array_diff(array_keys($files), $ran));
    }

    public function runOne(string $file): string
    {
        $buffer = new BufferedOutput();
        $migrator = app('migrator');
        $migrator->setOutput($buffer);
        $migrator->run([$file], ['pretend' => false]);

        return trim(strip_tags($buffer->fetch()));
    }

    public function findFile(string $name): ?string
    {
        foreach ([database_path('migrations'), database_path('upgrades')] as $path) {
            $file = $path . '/' . $name . '.php';
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }
}
