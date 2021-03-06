<?php

namespace App\Console\Commands;

use App\Models\DpsParse;
use App\Models\File;
use Cloudinary;
use Illuminate\Console\Command;

class PruneOrphanedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes orphaned files from Cloudinary.';

    private Cloudinary\Api $cloudinaryApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
        $this->cloudinaryApi = new Cloudinary\Api();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        // Get rid of soft-deleted DpsParses.
        DpsParse::query()->onlyTrashed()->forceDelete();

        $hashesToDelete = [];

        /** @var File[] $files */
        $files = File::query()->get();
        foreach ($files as $file) {
            $uploaders = $file->uploaders;
            if ($uploaders->count() === 0) {
                $this->warn($file->hash . ' Orphaned (no uploader).');
                $hashesToDelete[] = $file->hash;
                continue;
            }

            $asParseScreenshotOfDpsParse = $file->asParseScreenshotOfDpsParse;
            $asInfoScreenshotOfDpsParse = $file->asInfoScreenshotOfDpsParse;
            if ($asParseScreenshotOfDpsParse->count() === 0 && $asInfoScreenshotOfDpsParse->count() === 0) {
                $this->warn($file->hash . ' Orphaned (no usage).');
                $hashesToDelete[] = $file->hash;
            } else {
                $this->info($file->hash . ' Used.');
            }
        }

        $chunksOfHashesToDelete = array_chunk($hashesToDelete, 100);
        foreach ($chunksOfHashesToDelete as $id => $chunk) {
            try {
                $this->cloudinaryApi->delete_resources($chunk);
                $this->info('Pruned Chunk #' . $id . ' with ' . count($chunk) . ' files');
                File::destroy(collect($hashesToDelete));
            } catch (Cloudinary\Api\GeneralError $e) {
                $this->error('Failed to prune Chunk #' . $id . ' with ' . count($chunk) . ' files: ' . $e->getMessage());
            }
        }
        $this->info('Processed deletion of ' . count($hashesToDelete) . ' Orphaned Files from Cloudinary.');

        return true;
    }
}
