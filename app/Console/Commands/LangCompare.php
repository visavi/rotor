<?php

namespace App\Console\Commands;

use App\Support\Locale;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class LangCompare extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lang:compare {lang1} {lang2}';

    /**
     * The console command description.
     */
    protected $description = 'Compare lang files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lang1 = $this->argument('lang1');
        $lang2 = $this->argument('lang2');

        // Язык может лежать и в модуле-языке, не только в resources/lang
        $path1 = Locale::path($lang1);
        if (! $path1) {
            $this->error('Lang "' . $lang1 . '" not found');

            return 1;
        }

        $path2 = Locale::path($lang2);
        if (! $path2) {
            $this->error('Lang "' . $lang2 . '" not found');

            return 1;
        }

        $langFiles = glob($path1 . '/*.php') ?: [];

        foreach ($langFiles as $file) {
            $array1 = require $file;

            $otherFile = $path2 . '/' . basename($file);
            if (file_exists($otherFile)) {
                $array2 = require $otherFile;

                $diff1 = $this->arrayDiffKeyRecursive($array1, $array2);

                if ($diff1) {
                    $this->warn('Not keys in file "' . $lang2 . '/' . basename($otherFile) . '" (' . implode(', ', array_keys($diff1)) . ')');
                }

                $diff2 = $this->arrayDiffKeyRecursive($array2, $array1);

                if ($diff2) {
                    $this->warn('Extra keys in File "' . $lang1 . '/' . basename($otherFile) . '" (' . implode(', ', array_keys($diff2)) . ')');
                }

                if (empty($diff1) && empty($diff2)) {
                    $this->info('File "' . $lang1 . '/' . basename($otherFile) . '" identical!');
                }
            } else {
                $this->error('File "' . $lang2 . '/' . basename($otherFile) . '" not found!');
            }
        }

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Recursive array diff key
     */
    private function arrayDiffKeyRecursive(array $array1, array $array2): array
    {
        $diff = array_diff_key($array1, $array2);

        foreach ($array1 as $k => $v) {
            if (is_array($v) && is_array($array2[$k] ?? null)) {
                $diffRecursive = $this->arrayDiffKeyRecursive($array1[$k], $array2[$k]);

                if ($diffRecursive) {
                    $diff[$k] = $diffRecursive;
                }
            }
        }

        return $diff;
    }
}
