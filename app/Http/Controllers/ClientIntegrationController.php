<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ClientIntegrationController extends Controller
{
    public function index(): View
    {
        return view('pages.clients_integrations.index');
    }
}
