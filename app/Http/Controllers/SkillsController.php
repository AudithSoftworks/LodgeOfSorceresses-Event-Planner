<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SkillsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        app('cache.store')->has('skills'); // Trigger Recache listener.

        return response()->json(app('cache.store')->get('skills'));
    }
}
