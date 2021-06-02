<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LangCompare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:compare {lang1} {lang2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare lang files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lang1 = $this->argument('lang1');
        $lang2 = $this->argument('lang2');

        if (!file_exists(resource_path('lang/' . $lang1))) {
            $this->error('Lang "' . $lang1 . '" not found');
            return 1;
        }

        if (!file_exists(resource_path('lang/' . $lang2))) {
            $this->error('Lang "' . $lang2 . '" not found');
            return 1;
        }

        $langFiles = glob(resource_path('lang/' . $lang1 . '/*.php'));

        foreach ($langFiles as $file) {
            $array1 = require($file);

            $otherFile = str_replace('/' . $lang1 . '/', '/' . $lang2 . '/', $file);
            if (file_exists($otherFile)) {
                $array2 = require($otherFile);

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

        return 0;
    }

    /**
     * Recursive array diff key
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function arrayDiffKeyRecursive(array $array1, array $array2): array
    {
        $diff = array_diff_key($array1, $array2);

        foreach ($array1 as $k => $v) {
            if (is_array($array1[$k]) && is_array($array2[$k])) {
                $diffRecursive = $this->arrayDiffKeyRecursive($array1[$k], $array2[$k]);

                if ($diffRecursive) {
                    $diff[$k] = $diffRecursive;
                }
            }
        }

        return $diff;
    }
}
