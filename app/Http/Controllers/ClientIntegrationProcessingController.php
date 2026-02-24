<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ClientIntegrationProcessingController extends Controller
{
    public function process(Request $request)
    {
        $data = $request->all();

        $query = $this->loadTables();
        $query = $this->filters($query, $data);
        $query = $this->search($query, $data);
        $query = $this->ordering($query, $data);

        return $this->formatResults($query);
    }

    public function loadTables()
    {
        return DB::table('clients_integrations')
            ->leftJoin('clients', 'clients.id', '=', 'clients_integrations.client_id');
    }

    public function filters($query, array $data)
    {
        if (!empty($data['provider_filter']) && $data['provider_filter'] !== 'all') {
            $query->where('clients_integrations.provider', $data['provider_filter']);
        }

        if (!empty($data['type_filter']) && $data['type_filter'] !== 'all') {
            $query->where('clients_integrations.type', $data['type_filter']);
        }

        if (!empty($data['status_filter']) && $data['status_filter'] !== 'all') {
            $query->where('clients_integrations.status', $data['status_filter']);
        }

        return $query;
    }

    public function search($query, array $data)
    {
        $searchBy = $data['searchBy'] ?? ($data['search']['value'] ?? null);

        if (!empty($searchBy)) {
            $query->where(function ($sub) use ($searchBy) {
                $sub->where('clients_integrations.id', 'like', "%{$searchBy}%")
                    ->orWhere('clients_integrations.client_id', 'like', "%{$searchBy}%")
                    ->orWhere('clients.name', 'like', "%{$searchBy}%")
                    ->orWhere('clients_integrations.provider', 'like', "%{$searchBy}%")
                    ->orWhere('clients_integrations.type', 'like', "%{$searchBy}%")
                    ->orWhere('clients_integrations.external_account_id', 'like', "%{$searchBy}%");
            });
        }

        return $query;
    }

    public function ordering($query, array $data)
    {
        if (!empty($data['order'])) {
            $direction = $data['order'][0]['dir'];
            $index = $data['order'][0]['column'] ?? 0;
            $orderThis = $data['order_by'] ?? ($data['columns'][$index]['data'] ?? 'created_at');

            $column = match ($orderThis) {
                'id' => 'clients_integrations.id',
                'client' => 'clients.name',
                'provider' => 'clients_integrations.provider',
                'type' => 'clients_integrations.type',
                'external_account_id' => 'clients_integrations.external_account_id',
                'token_expires_at' => 'clients_integrations.token_expires_at',
                'status' => 'clients_integrations.status',
                'created_at' => 'clients_integrations.created_at',
                default => 'clients_integrations.created_at',
            };

            return $query->orderBy($column, $direction);
        }

        return $query->orderByDesc('clients_integrations.created_at');
    }

    public function formatResults($query)
    {
        $query->select(
            'clients_integrations.id',
            'clients_integrations.client_id',
            'clients.name as client_name',
            'clients_integrations.provider',
            'clients_integrations.type',
            'clients_integrations.external_account_id',
            'clients_integrations.token_expires_at',
            'clients_integrations.status',
            'clients_integrations.created_at'
        );

        return DataTables::query($query)
            ->addColumn('client', function ($row) {
                if (!empty($row->client_name)) {
                    return '<a href="' . route('clients.show', $row->client_id) . '" class="text-gray-700 text-hover-primary fw-bolder">' . e($row->client_name) . ' <span class="text-gray-500 fw-normal fs-8">#' . e($row->client_id) . '</span></a>';
                }

                return '<span class="text-gray-500">Cliente #' . e($row->client_id) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return match ((string) $row->status) {
                    'active' => '<span class="badge badge-light-success">Ativa</span>',
                    'expired' => '<span class="badge badge-light-warning">Expirada</span>',
                    'revoked' => '<span class="badge badge-light-danger">Revogada</span>',
                    'in_progress' => '<span class="badge badge-light-info">Em progresso</span>',
                    default => '<span class="badge badge-light">' . e($row->status) . '</span>',
                };
            })
            ->editColumn('provider', function ($row) {
                return $row->provider ? e($row->provider) : '-';
            })
            ->editColumn('type', function ($row) {
                return $row->type ? e($row->type) : '-';
            })
            ->editColumn('external_account_id', function ($row) {
                return $row->external_account_id ? e($row->external_account_id) : '-';
            })
            ->editColumn('token_expires_at', function ($row) {
                if (empty($row->token_expires_at)) {
                    return '<span class="badge badge-light">Sem expiração</span>';
                }

                $expiresAt = Carbon::parse($row->token_expires_at);
                $today = now()->startOfDay();
                $daysRemaining = $today->diffInDays($expiresAt->copy()->startOfDay(), false);
                $formattedDate = $expiresAt->format('d/m/Y H:i');

                if ($daysRemaining < 0) {
                    $daysExpired = abs($daysRemaining);
                    return '<span class="badge badge-light-danger">Expirado há ' . $daysExpired . ' dia(s)</span><br><span class="text-gray-500 fs-8">' . $formattedDate . '</span>';
                }

                if ($daysRemaining === 0) {
                    return '<span class="badge badge-light-warning">Expira hoje</span><br><span class="text-gray-500 fs-8">' . $formattedDate . '</span>';
                }

                if ($daysRemaining <= 7) {
                    return '<span class="badge badge-light-warning">' . $daysRemaining . ' dia(s)</span><br><span class="text-gray-500 fs-8">' . $formattedDate . '</span>';
                }

                if ($daysRemaining <= 30) {
                    return '<span class="badge badge-light-info">' . $daysRemaining . ' dia(s)</span><br><span class="text-gray-500 fs-8">' . $formattedDate . '</span>';
                }

                return '<span class="badge badge-light-success">' . $daysRemaining . ' dia(s)</span><br><span class="text-gray-500 fs-8">' . $formattedDate . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? date('d/m/Y H:i', strtotime($row->created_at))
                    : '-';
            })
            ->rawColumns(['client', 'status_badge', 'token_expires_at'])
            ->make(true);
    }
}
