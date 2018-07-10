<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        /**
         * @var \App\Services\IpsApi $api
         */
        $api = app('ips.api');
        $events = $api->getCalendarEvents();

        /** @var \App\Models\User $user */
        $user = app('auth.driver')->user();
        $name = $user->name;
        $userType = self::TRANSLATION_TAG_REGISTERED_USER;



        return view('index', ['userType' => $userType, 'name' => $name, 'events' => $events]);
    }
}
