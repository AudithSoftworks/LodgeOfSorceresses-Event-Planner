<?php

namespace App\Http\Controllers;

use App\Model\EquipmentSet;

class SetsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'sets' => EquipmentSet::query()->orderBy('name')->get()->keyBy('id')->toArray()
        ]);
    }
}
