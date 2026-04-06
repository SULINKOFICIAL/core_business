<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TenantIntegrationController extends Controller
{
    public function index(): View
    {
        return view('pages.tenants_integrations.index');
    }
}
