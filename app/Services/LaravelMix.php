<?php

namespace App\Services;

use Exception;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class LaravelMix
{
    /**
     * Get the path to a versioned Mix file.
     *
     * @param string $path
     * @param string $manifestDirectory
     *
     * @throws \Exception
     * @return \Illuminate\Support\HtmlString|string
     */
    public function __invoke(string $path, string $manifestDirectory = '')
    {
        static $manifests = [];

        if ($manifestDirectory && !Str::startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        if (is_file(public_path($manifestDirectory . '/hot'))) {
            $url = rtrim(file_get_contents(public_path($manifestDirectory . '/hot')));

            if (Str::startsWith($url, ['http://', 'https://'])) {
                return new HtmlString(Str::after($url, ':') . $path);
            }

            return new HtmlString("//localhost:8080{$path}");
        }

        $manifestPath = public_path($manifestDirectory . '/mix-manifest.json');

        if (!isset($manifests[$manifestPath])) {
            if (!is_file($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        }

        $manifest = $manifests[$manifestPath];

        if (!isset($manifest[$path])) {
            $exception = new Exception("Unable to locate Mix file: {$path}.");

            if (!app('config')->get('app.debug')) {
                report($exception);

                return $path;
            }

            throw $exception;
        }

        return new HtmlString(app('config')->get('app.mix_url') . $manifestDirectory . $manifest[$path]);
    }
}
