<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Coupon $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }

    /**
     * Display a listing of the coupons.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'DESC')->paginate(50);

        return view('pages.coupons.index')->with([
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return view('pages.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|in:percent,fixed,trial',
            'amount' => 'nullable|string',
            'trial_months' => 'nullable|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;

        if ($data['type'] === 'trial') {
            if (empty($data['trial_months'])) {
                return redirect()->back()->withErrors(['trial_months' => 'Informe os meses de teste.'])->withInput();
            }
            $data['amount'] = null;
        } else {
            if (empty($data['amount'])) {
                return redirect()->back()->withErrors(['amount' => 'Informe o valor do cupom.'])->withInput();
            }
            $data['amount'] = toDecimal($data['amount']);
            $amountValue = (float) $data['amount'];
            if ($data['type'] === 'percent' && ($amountValue < 0 || $amountValue > 100)) {
                return redirect()->back()->withErrors(['amount' => 'Percentual deve ser entre 0 e 100.'])->withInput();
            }
            if ($data['type'] === 'fixed' && $amountValue <= 0) {
                return redirect()->back()->withErrors(['amount' => 'Valor precisa ser maior que zero.'])->withInput();
            }
            $data['trial_months'] = null;
        }

        $created = $this->repository->create([
            'code' => $data['code'],
            'type' => $data['type'],
            'amount' => $data['amount'] ?? null,
            'trial_months' => $data['trial_months'] ?? null,
            'is_active' => $data['is_active'],
            'max_redemptions' => $data['max_redemptions'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('coupons.index')
            ->with('message', 'Cupom <b>'. $created->code . '</b> adicionado com sucesso.');
    }

    public function edit($id)
    {
        $coupon = $this->repository->find($id);

        if (!$coupon) {
            return redirect()->back();
        }

        return view('pages.coupons.edit')->with([
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, $id)
    {
        $coupon = $this->repository->find($id);

        if (!$coupon) {
            return redirect()->back();
        }

        $data = $request->all();

        $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|in:percent,fixed,trial',
            'amount' => 'nullable|string',
            'trial_months' => 'nullable|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;

        if ($data['type'] === 'trial') {
            if (empty($data['trial_months'])) {
                return redirect()->back()->withErrors(['trial_months' => 'Informe os meses de teste.'])->withInput();
            }
            $data['amount'] = null;
        } else {
            if (empty($data['amount'])) {
                return redirect()->back()->withErrors(['amount' => 'Informe o valor do cupom.'])->withInput();
            }
            $data['amount'] = toDecimal($data['amount']);
            $amountValue = (float) $data['amount'];
            if ($data['type'] === 'percent' && ($amountValue < 0 || $amountValue > 100)) {
                return redirect()->back()->withErrors(['amount' => 'Percentual deve ser entre 0 e 100.'])->withInput();
            }
            if ($data['type'] === 'fixed' && $amountValue <= 0) {
                return redirect()->back()->withErrors(['amount' => 'Valor precisa ser maior que zero.'])->withInput();
            }
            $data['trial_months'] = null;
        }

        $coupon->update([
            'code' => $data['code'],
            'type' => $data['type'],
            'amount' => $data['amount'] ?? null,
            'trial_months' => $data['trial_months'] ?? null,
            'is_active' => $data['is_active'],
            'max_redemptions' => $data['max_redemptions'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('coupons.index')
            ->with('message', 'Cupom <b>'. $coupon->code . '</b> atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $coupon = $this->repository->find($id);

        if (!$coupon) {
            return redirect()->back();
        }

        $coupon->update([
            'is_active' => !$coupon->is_active,
        ]);

        return redirect()
            ->back()
            ->with('message', 'Status do cupom atualizado com sucesso.');
    }
}
