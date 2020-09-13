<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * @throws \JsonException
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        /** @var \Illuminate\Support\ViewErrorBag $errorBag */
        $errorBag = Session::get('errors');
        $errors = $errorBag !== null ? $errorBag->all() : [];

        return view('layout', [
            'errors' => json_encode($errors, JSON_THROW_ON_ERROR),
        ]);
    }
}
