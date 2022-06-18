<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

/**
 * Class HomeController.
 */
class HomeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (auth()->check()) {
            return redirect('/banner');
        } 
        return view('frontend.index');
    }
}
