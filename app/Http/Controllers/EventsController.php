<?php

namespace App\Http\Controllers;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /**
         * @var \App\Services\IpsApi $api
         */
        $api = app('ips.api');

        return response()->json([
            'events' => $api->getCalendarEvents(),
        ]);
    }
}
