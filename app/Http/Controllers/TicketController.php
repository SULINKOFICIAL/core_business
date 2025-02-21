<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Ticket $content)
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
        // Obtém dados
        $contents = $this->repository->all();

        // Retorna a página
        return view('pages.tickets.index')->with([
            'contents' => $contents,
        ]);
    }
}
