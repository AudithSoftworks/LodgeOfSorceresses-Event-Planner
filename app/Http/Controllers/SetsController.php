<?php

namespace App\Http\Controllers;

class SetsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        app('cache.store')->has('equipmentSets'); // Trigger Recache listener.

        return response()->json([
            'sets' => app('cache.store')->get('equipmentSets')
        ]);
    }
}
