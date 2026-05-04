<?php

namespace App\Http\Controllers;

class TenantDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retorna a página. Os dados são carregados por AJAX (DataTables server-side).
        return view('pages.tenants_domains.index');
    }
}
