<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Gate;

class BuyerController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('scope:read-general')->only('index');
        $this->middleware('can:view,buyer')->only('show');
    }


    public function index()
    {
        $this->allowedAdminAction(); // gate

        //$buyers = Buyer::has('transactions')->get();

        $buyers = Buyer::all();

        return $this->showAll($buyers);
    }


    public function show(Buyer $buyer)
    {
        //$buyer = Buyer::has('transactions')->findOrFail($id);

        return $this->showOne($buyer);
    }
}
