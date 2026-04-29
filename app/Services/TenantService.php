<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class TenantService
{
    public function systemStatus(Tenant $tenant): string
    {
        if (!$tenant->token) {
            return 'Token Empty';
        }

        try {
            $domain = $tenant->domains()->orderBy('id')->value('domain');
            if (!$domain) {
                return 'Error';
            }

            $response = Http::withToken($tenant->token)->get("https://{$domain}/api/sistema/status");

            if ($response->successful() && ($response->json()['status'] ?? null) === 'ok') {
                return 'OK';
            }

            return 'Error';
        } catch (\Throwable $e) {
            return 'Error';
        }
    }
}

