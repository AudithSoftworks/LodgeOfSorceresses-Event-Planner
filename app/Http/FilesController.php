<?php

namespace App\Http\Controllers;

use App\Exceptions\Common\ValidationException;
use App\Exceptions\FileStream as FileStreamExceptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;

class FilesController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        return response()->json($me->files->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\Common\ValidationException
     * @throws \ErrorException
     */
    public function store(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'qquuid' => 'required|string|size:36',
            'qqfilename' => 'required|string',
            'qqtotalfilesize' => 'required|numeric',
            'qqtag' => 'required|string|in:' . implode(',', array_keys(config('filesystems.allowed_tags_and_limits'))),
            'qqtotalparts' => 'required_with_all:qqpartindex,qqpartbyteoffset,qqchunksize|numeric',
            'qqpartindex' => 'required_with_all:qqtotalparts,qqpartbyteoffset,qqchunksize|numeric',
            'qqpartbyteoffset' => 'required_with_all:qqpartindex,qqtotalparts,qqchunksize|numeric',
            'qqchunksize' => 'required_with_all:qqpartindex,qqpartbyteoffset,qqtotalparts|numeric',
            'qqresume' => 'sometimes|required_with_all:qqpartindex,qqpartbyteoffset,qqtotalparts,qqchunksize|string'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (strpos($request->header('content-type'), 'multipart/form-data') === false && !$request->has('post-process')) {
            throw new FileStreamExceptions\UploadRequestIsNotMultipartFormDataException;
        }
        $request->has('qqresume') && $request->get('qqresume') === 'true' && app('filestream')->isUploadResumable($request);

        return app('filestream')->handleUpload($request);
    }

    /**
     * @param string $hash
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $hash): JsonResponse
    {
        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        if (!($file = $me->files()->where('hash', $hash)->get())) {
            throw new ModelNotFoundException();
        }

        return response()
            ->json([
                'data' => file_get_contents(app('filestream')->getAbsolutePath($file->path)),
                'mime' => $file->mime,
            ])
            ->header("pragma", "private")
            ->header("Cache-Control", " private, max-age=86400");
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string                   $qquuid
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $qquuid): JsonResponse
    {
        app('filestream')->deleteFile($qquuid, $request->get('tag'));

        return response()->json()->setStatusCode(IlluminateResponse::HTTP_NO_CONTENT);
    }
}