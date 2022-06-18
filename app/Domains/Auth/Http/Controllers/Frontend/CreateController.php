<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

/**
 * Class HomeController.
 */
class CreateController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function product()
    {
        return view('frontend.create.create_Product');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function nft()
    {
        return view('frontend.create.create_NFT');
    }

}
