<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $request;
    private $repository;
    private $orderService;

    public function __construct(Request $request, Order $content)
    {
        $this->request = $request;
        $this->repository = $content;
        $this->orderService = new OrderService;
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {

        // Aprova o pagamento do cliente
        $this->orderService->confirmPaymentOrder($id);

        // Redireciona
        return redirect()
            ->back()
            ->with('success', 'Nenhuma assinatura vencida encontrada');

    }
}
