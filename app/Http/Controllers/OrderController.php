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
     * Display a listing of the orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.orders.index');
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

        if (request()->ajax()) {
            return view('pages.clients._order')->with([
                'order' => $order,
            ]);
        }

        return view('pages.orders.show')->with([
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

        // Busca o pedido
        $order = Order::find($id);

        // Aprova o pagamento do cliente
        $this->orderService->confirmPaymentOrder($order);

        // Redireciona
        return redirect()
            ->back()
            ->with('success', 'Nenhuma assinatura vencida encontrada');

    }
}
