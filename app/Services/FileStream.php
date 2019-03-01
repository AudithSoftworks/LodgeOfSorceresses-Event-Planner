<?php namespace App\Services;

use App\Events\Files\Uploaded;
use App\Exceptions\FileStream as FileStreamExceptions;
use App\Models\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStream
{
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    public $filesystem;

    /**
     * Folder to hold uploaded chunks.
     *
     * @var string
     */
    public $temporaryChunksFolder = '/_chunks';

    /**
     * Chunks will be cleaned once in 1000 requests on average.
     *
     * @var float
     */
    public $chunksCleanupProbability = 0.001;

    /**
     * By default, chunks are considered loose and deletable, in 1 week.
     *
     * @var int
     */
    public $chunksExpireIn;

    /**
     * Upload size limit.
     *
     * @var int
     */
    public $sizeLimit;

    /**
     * FileStream constructor.
     */
    public function __construct()
    {
        $this->filesystem = app('filesystem');
        $this->chunksExpireIn = config('filesystems.disks.public.chunks_expire_in');
        if (app('config')->has('filesystems.chunks_ttl') && is_int(config('filesystems.chunks_ttl'))) {
            $this->chunksExpireIn = config('filesystems.chunks_ttl');
        }
        if (app('config')->has('filesystems.size_limit') && is_int(config('filesystems.size_limit'))) {
            $this->sizeLimit = config('filesystems.size_limit');
        }
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * Write the uploaded file to the local filesystem.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \ErrorException
     */
    public function handleUpload(Request $request)
    {
        $fineUploaderUuid = null;
        if ($request->has('qquuid')) {
            $fineUploaderUuid = $request->get('qquuid');
        }

        //------------------------------
        // Is it Post-processing?
        //------------------------------

        if ($request->has('post-process') && $request->get('post-process') == 1) {
            $this->combineChunks($request);

            return collect(event(new Uploaded($fineUploaderUuid, $request)))->last(); // Return the result of the second event listener.
        }

        //----------------
        // Prelim work.
        //----------------

        if (!file_exists($this->temporaryChunksFolder) || !is_dir($this->temporaryChunksFolder)) {
            $this->filesystem->makeDirectory($this->temporaryChunksFolder);
        }

        # Temp folder writable?
        if (!is_writable($absolutePathToTemporaryChunksFolder = $this->getAbsolutePath($this->temporaryChunksFolder)) || !is_executable($absolutePathToTemporaryChunksFolder)) {
            throw new FileStreamExceptions\TemporaryUploadFolderNotWritableException;
        }

        # Cleanup chunks.
        if (1 === mt_rand(1, 1 / $this->chunksCleanupProbability)) {
            $this->cleanupChunks();
        }

        # Check upload size against the size-limit, if any.
        if (!empty($this->sizeLimit)) {
            $uploadIsTooLarge = false;
            $request->has('qqtotalfilesize') && intval($request->get('qqtotalfilesize')) > $this->sizeLimit && $uploadIsTooLarge = true;
            $this->filesizeFromHumanReadableToBytes(ini_get('post_max_size')) < $this->sizeLimit && $uploadIsTooLarge = true;
            $this->filesizeFromHumanReadableToBytes(ini_get('upload_max_filesize')) < $this->sizeLimit && $uploadIsTooLarge = true;
            if ($uploadIsTooLarge) {
                throw new FileStreamExceptions\UploadTooLargeException;
            }
        }

        # Is there attempt for multiple file uploads?
        $collectionOfUploadedFiles = collect($request->file());
        if ($collectionOfUploadedFiles->count() > 1) {
            throw new FileStreamExceptions\MultipleSimultaneousUploadsNotAllowedException;
        }

        /** @var UploadedFile $file */
        $file = $collectionOfUploadedFiles->first();

        //--------------------
        // Upload handling.
        //--------------------

        if ($file->getSize() == 0) {
            throw new FileStreamExceptions\UploadIsEmptyException;
        }

        $name = $file->getClientOriginalName();
        if ($request->has('qqfilename')) {
            $name = $request->get('qqfilename');
        }
        if (empty($name)) {
            throw new FileStreamExceptions\UploadFilenameIsEmptyException;
        }

        $totalNumberOfChunks = $request->has('qqtotalparts') ? (int)$request->get('qqtotalparts') : 1;

        if ($totalNumberOfChunks > 1) {
            $chunkIndex = intval($request->get('qqpartindex'));
            $targetFolder = $this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid;
            if (!$this->filesystem->exists($targetFolder)) {
                try {
                    $this->filesystem->makeDirectory($targetFolder);
                } /** @noinspection PhpRedundantCatchClauseInspection */ catch (\ErrorException $e) {
                    if (!$this->filesystem->exists($targetFolder)) {
                        /** @noinspection PhpUnhandledExceptionInspection */
                        throw $e;
                    }
                }
            }

            if (!$file->isValid()) {
                throw new FileStreamExceptions\UploadAttemptFailedException;
            }
            $file->move($this->getAbsolutePath($targetFolder), $chunkIndex);

            return response()->json(['success' => true, 'uuid' => $fineUploaderUuid]);
        } else {
            if (!$file->isValid()) {
                throw new FileStreamExceptions\UploadAttemptFailedException;
            }
            $file->move($this->getAbsolutePath(''), $fineUploaderUuid);

            return collect(event(new Uploaded($fineUploaderUuid, $request)))->last(); // Return the result of the second event listener.
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function isUploadResumable(Request $request)
    {
        $fineUploaderUuid = $request->get('qquuid');
        $chunkIndex = intval($request->get('qqpartindex'));
        $numberOfExistingChunks = count($this->filesystem->files($this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid));
        if ($numberOfExistingChunks < $chunkIndex) {
            throw new FileStreamExceptions\UploadIncompleteException;
        }

        return true;
    }

    /**
     * @param string $size
     *
     * @return false|string
     */
    public function filesizeFromHumanReadableToBytes($size)
    {
        if (preg_match('/^([\d,.]+)\s?([kmgtpezy]?i?b)$/i', $size, $matches) !== 1) {
            return false;
        }
        $coefficient = $matches[1];
        $prefix = strtolower($matches[2]);

        $binaryPrefices = ['b', 'kib', 'mib', 'gib', 'tib', 'pib', 'eib', 'zib', 'yib'];
        $decimalPrefices = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];

        $base = in_array($prefix, $binaryPrefices) ? 1024 : 1000;
        $flippedPrefixMap = $base == 1024 ? array_flip($binaryPrefices) : array_flip($decimalPrefices);
        $factor = array_pull($flippedPrefixMap, $prefix);

        return sprintf("%d", bcmul(str_replace(',', '', $coefficient), bcpow($base, $factor)));
    }

    /**
     * @param int  $bytes
     * @param int  $decimals
     * @param bool $binary
     *
     * @return string
     */
    public function filesizeFromBytesToHumanReadable($bytes, $decimals = 2, $binary = true)
    {
        $binaryPrefices = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        $decimalPrefices = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = intval(floor((strlen($bytes) - 1) / 3));

        return sprintf("%.{$decimals}f", $bytes / pow($binary ? 1024 : 1000, $factor)) . ' ' . $binary ? $binaryPrefices[$factor] : $decimalPrefices[$factor];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAbsolutePath($path)
    {
        return $this->filesystem->path(trim($path, DIRECTORY_SEPARATOR));
    }

    /**
     * @param File $file
     *
     * @return array
     */
    public function url(File $file): array
    {
        $large = cloudinary_url($file->hash, [
            'secure' => true,
            'width' => 800,
            'height' => 800,
            'gravity' => 'auto:classic',
            'crop' => 'fill'
        ]);

        $thumbnail = cloudinary_url($file->hash, [
            'secure' => true,
            'width' => 100,
            'height' => 100,
            'gravity' => 'auto:classic',
            'crop' => 'fill'
        ]);

        return ['thumbnail' => $thumbnail, 'large' => $large];
    }

    /**
     * @param string $qquuid
     * @param string $tag
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteFile($qquuid, $tag = '')
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        foreach ($me->files as $file) {
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $file->pivot;
            /** @noinspection PhpUndefinedFieldInspection */
            $tagCheck = (!empty($tag) && $pivot->tag === $tag) || empty($tag);
            /** @noinspection PhpUndefinedFieldInspection */
            if ($pivot->qquuid === $qquuid && $tagCheck) {
                $pivot->delete();

                $file->load('uploaders');
                !$file->uploaders->count() && app('filesystem')->disk($file->disk)->delete($file->path) && $file->delete();
            }
        }

        return true;
    }

    private function cleanupChunks()
    {
        foreach ($this->filesystem->directories($this->temporaryChunksFolder) as $file) {
            if (time() - $this->filesystem->lastModified($file) > $this->chunksExpireIn) {
                $this->filesystem->deleteDirectory($file);
            }
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    private function combineChunks(Request $request)
    {
        # Prelim
        $fineUploaderUuid = $request->get('qquuid');
        $chunksFolder = $this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid;
        $totalNumberOfChunks = $request->has('qqtotalparts') ? intval($request->get('qqtotalparts')) : 1;

        # Do we have all chunks?
        $numberOfExistingChunks = count($this->filesystem->files($chunksFolder));
        if ($numberOfExistingChunks != $totalNumberOfChunks) {
            throw new FileStreamExceptions\UploadIncompleteException;
        }

        # We have all chunks, proceed with combine.
        $targetStream = fopen($this->getAbsolutePath($fineUploaderUuid), 'wb');
        for ($i = 0; $i < $totalNumberOfChunks; $i++) {
            $chunkStream = fopen($this->getAbsolutePath($chunksFolder . DIRECTORY_SEPARATOR . $i), 'rb');
            stream_copy_to_stream($chunkStream, $targetStream);
            fclose($chunkStream);
        }
        fclose($targetStream);
        $this->filesystem->deleteDirectory($chunksFolder);
    }
}
