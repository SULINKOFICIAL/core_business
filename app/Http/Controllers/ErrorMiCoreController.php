<?php

namespace App\Http\Controllers;

use App\Models\ErrorMiCore;
use Illuminate\Http\Request;

class ErrorMiCoreController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, ErrorMiCore $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retorna a página
        return view('pages.errors.index');
    }

    public function show()
    {
        // Obtém dados
        $contents = $this->repository->all();

        // Retorna a página
        return view('pages.errors.show')->with([
            'contents' => $contents,
        ]);
    }
}
