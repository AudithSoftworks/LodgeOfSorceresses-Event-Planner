<?php namespace App\Listeners\Files;

use App\Events\Files\Uploaded;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class PersistUploadedFile
{
    /**
     * @param \App\Events\Files\Uploaded $event
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function handle(Uploaded $event): JsonResponse
    {
        $uploadUuid = $event->uploadUuid;
        $request = $event->request;
        /** @noinspection PhpUndefinedMethodInspection */
        $pathPrefix = app('filesystem.disk')->getDriver()->getAdapter()->getPathPrefix();
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        /*
        |--------------------------------------------------
        | Move file to its final permanent destination.
        |--------------------------------------------------
        */

        $hash = hash_file('sha256', app('filestream')->getAbsolutePath($uploadUuid));
        $destination = 'public/' . $hash;
        if (config('filesystems.load_balancing.enabled')) {
            $config = config('filesystems.load_balancing');
            $folders = [];
            for ($i = 0; $i < $config['depth']; $i++) {
                $folders[] = substr($hash, -1 * ($i + 1) * $config['length'], $config['length']);
            }
            $destination = 'public/' . implode(DIRECTORY_SEPARATOR, array_merge($folders, [$hash]));
        }
        $filesystem = app('filesystem');
        (!$filesystem->exists($destination)) ? $filesystem->move($uploadUuid, $destination) : $filesystem->delete($uploadUuid);

        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
        \Cloudinary\Uploader::upload($pathPrefix . $destination, [
            'public_id' => $hash,
            'use_filename' => true,
            'unique_filename' => false,
            'tags' => ['dps-parse', 'user-' . $me->id],
            'quality_analysis' => true,
            'phash' => true,
            'eager' => [
                [
                    'width' => 140,
                ]
            ]
        ]);

        /*
        |---------------------------------
        | Check the tag and its limit.
        |---------------------------------
        */

        $tag = $request->get('qqtag');
        $tagLimit = config('filesystems.allowed_tags_and_limits.' . $tag);
        if ($tagLimit > 0) {
            $allFilesWithSameTagBelongingToUser = $me->load([
                'files' => static function (BelongsToMany $query) use ($tag) {
                    $query->wherePivot('tag', '=', $tag);
                }
            ])->files;
            if (($numberOfFilesToDeleteToComplyWitTagLimit = $allFilesWithSameTagBelongingToUser->count() - $tagLimit + 1) > 0) {
                while ($numberOfFilesToDeleteToComplyWitTagLimit > 0) {
                    $pivotToDelete = $allFilesWithSameTagBelongingToUser->shift();
                    app('filestream')->deleteFile($pivotToDelete->hash, $tag);
                    $numberOfFilesToDeleteToComplyWitTagLimit--;
                }
            }
        }

        /*
        |------------------------------------
        | Persist file record in database.
        |------------------------------------
        */

        $uploadedFile = new SymfonyFile(app('filestream')->getAbsolutePath($destination));
        if (!$file = File::find($hash = $uploadedFile->getFilename())) {
            $file = new File();
            $file->hash = $hash;
            $file->disk = 'local';
            $file->path = trim(str_replace($pathPrefix, '', $uploadedFile->getPathname()), DIRECTORY_SEPARATOR);
            $file->mime = $uploadedFile->getMimeType();
            $file->size = $uploadedFile->getSize();
            $file->save();
        }

        $fileUserPivot = $me->files()->newPivotStatement();
        $fileUserPivot->whereTag($tag)->whereFileHash($hash)->delete();

        $file->uploaders()->attach([
            $me->id => [
                'qquuid' => $request->get('qquuid'),
                'original_client_name' => $request->get('qqfilename'),
                'tag' => $tag
            ]
        ]);

        $filesystem->delete($destination); // Delete files from local, Cloudinary copy will suffice!

        return response()->json(['success' => true, 'hash' => $file->hash])->setStatusCode(Response::HTTP_CREATED);
    }
}
