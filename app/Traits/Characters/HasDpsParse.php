<?php

namespace App\Traits\Characters;

use App\Models\DpsParse;
use App\Models\File;
use UnexpectedValueException;

trait HasDpsParse
{
    public function parseDpsParseSets(DpsParse $dpsParse): array
    {
        app('cache.store')->has('sets'); // Trigger Recache listener.
        $sets = app('cache.store')->get('sets');

        $setsUsedInDpsParse = array_filter($sets, static function ($key) use ($dpsParse) {
            return in_array($key, explode(',', $dpsParse->sets), false);
        }, ARRAY_FILTER_USE_KEY);

        return array_values($setsUsedInDpsParse);
    }

    public function parseScreenshotFiles(DpsParse $dpsParse): void
    {
        $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
        $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
        if (!$parseFile || !$superstarFile) {
            throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
        }
        $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
        $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
    }
}
