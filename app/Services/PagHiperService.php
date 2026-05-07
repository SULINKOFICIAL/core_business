<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PagHiperService
{
    /**
     *
     * Consulta a notificação da PagHiper pelo transaction_id e notification_id.
     *
     */
    public function notification(string $url, string $transactionId, string $notificationId): array
    {
        $response = Http::acceptJson()
            ->timeout(20)
            ->post($url . '/invoice/notification/', [
                'apiKey'          => env('PAG_HIPER_API_KEY'),
                'token'           => env('PAG_HIPER_TOKEN'),
                'transaction_id'  => $transactionId,
                'notification_id' => $notificationId,
            ]);
        return $response->json();
    }
}
