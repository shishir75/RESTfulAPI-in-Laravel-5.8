<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('scope:read-general')->only('show');
        $this->middleware('can:view,seller')->only('show');
    }


    public function index()
    {
        $this->allowedAdminAction(); // gate

        $sellers = Seller::has('products')->get();
        // $sellers = Seller::all();  // same

        return $this->showAll($sellers);
    }

    /**
     * Display the specified resource.
     *
     * @param Seller $seller
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)
    {
        //$seller = Seller::has('products')->findOrFail($id);

        return $this->showOne($seller);
    }
}
