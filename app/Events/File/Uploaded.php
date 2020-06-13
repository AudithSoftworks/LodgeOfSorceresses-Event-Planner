<?php namespace App\Events\File;

use Illuminate\Http\Request;

class Uploaded
{
    public ?string $uploadUuid;

    public Request $request;

    public function __construct(?string $uploadUuid, Request $request)
    {
        $this->uploadUuid = $uploadUuid;
        $this->request = $request;
    }
}
