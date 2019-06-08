<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\UserOAuth;
use GuzzleHttp\Exception\RequestException;
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

    /**
     * @var \Cloudinary\Api
     */
    private $cloudinaryApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
        $this->cloudinaryApi = new \Cloudinary\Api();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        $hashesToDelete = [];

        /** @var File[] $files */
        $files = File::query()->get();
        foreach ($files as $file) {
            $uploaders = $file->uploaders;
            if ($uploaders->count() === 0) {
                $this->info($file->hash . ' Orphaned.');
                $hashesToDelete[] = $file->hash;
            }
        }

        $chunksOfHashesToDelete = array_chunk($hashesToDelete, 100);
        foreach ($chunksOfHashesToDelete as $id => $chunk) {
            try {
                $this->cloudinaryApi->delete_resources($chunk);
            } catch (\Cloudinary\Api\GeneralError $e) {
                $this->error('Failed to delete Chunk #' . $id . ' with ' . count($chunk) . ' files.');
            }
        }
        $this->info('Processed deletion of ' . count($hashesToDelete) . ' Orphaned Files from Cloudinary.');

        return true;
    }
}
