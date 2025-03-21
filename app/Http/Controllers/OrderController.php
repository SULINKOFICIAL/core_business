<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    protected $request;
    private $repository;
    private $cpanelMiCore;

    public function __construct(Request $request, Order $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtém dados do Lead
        $order = $this->repository->find($id);

        // Retorna a página
        return view('pages.clients._order')->with([
            'order' => $order,
        ]);

    }
}
