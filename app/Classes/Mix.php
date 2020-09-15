<?php

declare(strict_types=1);

namespace App\Classes;

use Exception;
use Illuminate\Support\Str;

class Mix
{
    /**
     * Get the path to a versioned Mix file.
     *
     * @param string $path
     * @param string $manifestDirectory
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(string $path, string $manifestDirectory = '')
    {
        static $manifests = [];

        if (! Str::startsWith($path, '/')) {
            $path = "/{$path}";
        }

        if ($manifestDirectory && ! Str::startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        if (file_exists(HOME . $manifestDirectory . '/hot')) {
            $url = rtrim(file_get_contents(HOME . $manifestDirectory.'/hot'));

            if (Str::startsWith($url, ['http://', 'https://'])) {
                return Str::after($url, ':') . $path;
            }

            return "//localhost:8080{$path}";
        }

        $manifestPath = HOME . $manifestDirectory.'/mix-manifest.json';

        if (! isset($manifests[$manifestPath])) {
            if (! file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (! isset($manifest[$path])) {
            throw new Exception("Unable to locate Mix file: {$path}.");
        }

        return $manifestDirectory . $manifest[$path];
    }
}
