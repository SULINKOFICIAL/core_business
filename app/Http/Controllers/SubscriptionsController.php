<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request;

    }

}
