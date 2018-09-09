<?php namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = app('auth.driver')->user();
        $name = $user->name;
        $userType = self::TRANSLATION_TAG_REGISTERED_USER;

        return view('index', ['userType' => $userType, 'name' => $name]);
    }
}
