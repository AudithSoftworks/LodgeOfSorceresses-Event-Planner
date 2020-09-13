<?php namespace App\Services;

use App\Events\File\Uploaded;
use App\Exceptions\FileStream as FileStreamExceptions;
use App\Models\File;
use Cloudinary;
use ErrorException;
use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStream
{
    public FactoryContract $filesystem;

    /**
     * Folder to hold uploaded chunks.
     */
    public string $temporaryChunksFolder = '/_chunks';

    /**
     * Chunks will be cleaned once in 1000 requests on average.
     */
    public float $chunksCleanupProbability = 0.001;

    /**
     * By default, chunks are considered loose and deletable, in 1 week.
     */
    public ?int $chunksExpireIn;

    /**
     * Upload size limit.
     */
    public ?int $sizeLimit;

    /**
     * FileStream constructor.
     */
    public function __construct()
    {
        $this->filesystem = app('filesystem');
        $this->chunksExpireIn = config('filesystems.disks.public.chunks_expire_in');
        if (is_int($chunksTtl = config('filesystems.chunks_ttl'))) {
            $this->chunksExpireIn = $chunksTtl;
        }
        if (is_int($sizeLimit = config('filesystems.size_limit'))) {
            $this->sizeLimit = $sizeLimit;
        }
        Cloudinary::config([
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
     * @throws ErrorException
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleUpload(Request $request): JsonResponse
    {
        $fineUploaderUuid = null;
        if ($request->has('qquuid')) {
            $fineUploaderUuid = $request->get('qquuid');
        }

        //------------------------------
        // Is it Post-processing?
        //------------------------------

        if ($request->has('post-process') && $request->get('post-process') === '1') {
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
            throw new FileStreamExceptions\TemporaryUploadFolderNotWritableException();
        }

        # Cleanup chunks.
        if (1 === random_int(1, (int)(1 / $this->chunksCleanupProbability))) {
            $this->cleanupChunks();
        }

        # Check upload size against the size-limit, if any.
        if (!empty($this->sizeLimit)) {
            $uploadIsTooLarge = false;
            $request->has('qqtotalfilesize') && (int)$request->get('qqtotalfilesize') > $this->sizeLimit && $uploadIsTooLarge = true;
            $this->filesizeFromHumanReadableToBytes(ini_get('post_max_size')) < $this->sizeLimit && $uploadIsTooLarge = true;
            $this->filesizeFromHumanReadableToBytes(ini_get('upload_max_filesize')) < $this->sizeLimit && $uploadIsTooLarge = true;
            if ($uploadIsTooLarge) {
                throw new FileStreamExceptions\UploadTooLargeException();
            }
        }

        # Is there attempt for multiple file uploads?
        $collectionOfUploadedFiles = collect($request->file());
        if ($collectionOfUploadedFiles->count() > 1) {
            throw new FileStreamExceptions\MultipleSimultaneousUploadsNotAllowedException();
        }

        /** @var UploadedFile $file */
        $file = $collectionOfUploadedFiles->first();

        //--------------------
        // Upload handling.
        //--------------------

        if ($file->getSize() === 0) {
            throw new FileStreamExceptions\UploadIsEmptyException();
        }

        $name = $file->getClientOriginalName();
        if ($request->has('qqfilename')) {
            $name = $request->get('qqfilename');
        }
        if (empty($name)) {
            throw new FileStreamExceptions\UploadFilenameIsEmptyException();
        }

        $totalNumberOfChunks = $request->has('qqtotalparts') ? (int)$request->get('qqtotalparts') : 1;

        if ($totalNumberOfChunks > 1) {
            $chunkIndex = (int)$request->get('qqpartindex');
            $targetFolder = $this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid;
            if (!$this->filesystem->exists($targetFolder)) {
                try {
                    $this->filesystem->makeDirectory($targetFolder);
                } catch (ErrorException $e) {
                    if (!$this->filesystem->exists($targetFolder)) {
                        throw $e;
                    }
                }
            }

            if (!$file->isValid()) {
                throw new FileStreamExceptions\UploadAttemptFailedException();
            }
            $file->move($this->getAbsolutePath($targetFolder), (string)$chunkIndex);

            return response()->json(['success' => true, 'uuid' => $fineUploaderUuid]);
        }

        if (!$file->isValid()) {
            throw new FileStreamExceptions\UploadAttemptFailedException();
        }
        $file->move($this->getAbsolutePath(''), $fineUploaderUuid);

        return collect(event(new Uploaded($fineUploaderUuid, $request)))->last(); // Return the result of the second event listener.
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function isUploadResumable(Request $request): bool
    {
        $fineUploaderUuid = $request->get('qquuid');
        $chunkIndex = (int)$request->get('qqpartindex');
        $numberOfExistingChunks = count($this->filesystem->files($this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid));
        if ($numberOfExistingChunks < $chunkIndex) {
            throw new FileStreamExceptions\UploadIncompleteException();
        }

        return true;
    }

    public function filesizeFromHumanReadableToBytes(string $size): ?string
    {
        if (preg_match('/^([\d,.]+)\s?([kmgtpezy]?i?b)$/i', $size, $matches) !== 1) {
            return false;
        }
        $coefficient = $matches[1];
        $prefix = strtolower($matches[2]);

        $binaryPrefices = ['b', 'kib', 'mib', 'gib', 'tib', 'pib', 'eib', 'zib', 'yib'];
        $decimalPrefices = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];

        $base = in_array($prefix, $binaryPrefices, true) ? 1024 : 1000;
        $flippedPrefixMap = $base === 1024 ? array_flip($binaryPrefices) : array_flip($decimalPrefices);
        $factor = Arr::pull($flippedPrefixMap, $prefix);

        return sprintf('%d', bcmul(str_replace(',', '', $coefficient), bcpow((string)$base, $factor)));
    }

    public function filesizeFromBytesToHumanReadable(int $bytes, int $decimals = 2, bool $binary = true): string
    {
        $binaryPrefices = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        $decimalPrefices = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = (int)floor((strlen($bytes) - 1) / 3);

        return (sprintf("%.{$decimals}f", $bytes / (($binary ? 1024 : 1000) ** $factor)) . ' ' . $binary) ? $binaryPrefices[$factor] : $decimalPrefices[$factor];
    }

    public function getAbsolutePath(string $path): string
    {
        return $this->filesystem->path(trim($path, DIRECTORY_SEPARATOR));
    }

    public function url(File $file): array
    {
        $large = cloudinary_url($file->hash, [
            'secure' => true,
        ]);

        $thumbnail = cloudinary_url($file->hash, [
            'secure' => true,
            'width' => 140,
        ]);

        return ['thumbnail' => $thumbnail, 'large' => $large];
    }

    /**
     * @param string $qquuid
     * @param string $tag
     *
     * @throws \Exception
     * @return bool
     */
    public function deleteFile(string $qquuid, string $tag = ''): bool
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();

        foreach ($me->files as $file) {
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $file->pivot;
            /** @noinspection PhpUndefinedFieldInspection */
            $tagCheck = (!empty($tag) && $pivot->tag === $tag) || empty($tag);
            /** @noinspection PhpUndefinedFieldInspection */
            if ($pivot->qquuid === $qquuid && $tagCheck) {
                $pivot->delete();
                $file->loadMissing('uploaders');
                !$file->uploaders->count() && Storage::disk($file->disk)->delete($file->path) && $file->delete();
            }
        }

        return true;
    }

    private function cleanupChunks(): void
    {
        foreach ($this->filesystem->directories($this->temporaryChunksFolder) as $file) {
            if (time() - $this->filesystem->lastModified($file) > $this->chunksExpireIn) {
                $this->filesystem->deleteDirectory($file);
            }
        }
    }

    private function combineChunks(Request $request): void
    {
        # Prelim
        $fineUploaderUuid = $request->get('qquuid');
        $chunksFolder = $this->temporaryChunksFolder . DIRECTORY_SEPARATOR . $fineUploaderUuid;
        $totalNumberOfChunks = $request->has('qqtotalparts') ? (int)$request->get('qqtotalparts') : 1;

        # Do we have all chunks?
        $numberOfExistingChunks = count($this->filesystem->files($chunksFolder));
        if ($numberOfExistingChunks !== $totalNumberOfChunks) {
            throw new FileStreamExceptions\UploadIncompleteException();
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
