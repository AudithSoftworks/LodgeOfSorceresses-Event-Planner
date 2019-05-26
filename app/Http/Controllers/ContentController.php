<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        app('cache.store')->has('content'); // Trigger Recache listener.

        return response()->json(app('cache.store')->get('content'));
    }
}
