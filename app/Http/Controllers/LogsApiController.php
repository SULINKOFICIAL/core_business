<?php

namespace App\Http\Controllers;

class LogsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.logs.apis.index');
    }
}
