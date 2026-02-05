<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GuzzleService;
use Illuminate\Support\Facades\Log;

class ScheduleDispatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $guzzleService;
    protected $jobName;
    protected $jobData;

    public function __construct($jobName, $jobData = [])
    {
        $this->jobName = $jobName;
        $this->jobData = $jobData;
        $this->guzzleService = new GuzzleService();
    }

    public function handle()
    {
        // Busca todos os tenants ativos
        $clients = Client::where('status', true)->get();

        /**
         * Envia o comando para cada cliente.
         */
        foreach ($clients as $client) {

            // Realiza solicitação
            $this->guzzleService->request(
                'post', 
                'sistema/processar-tarefa', 
                $client, 
                [
                    'job' => $this->jobName,
                    'data' => $this->jobData,
                ]
            );

            Log::info('Disparo de job realizado para cliente', [
                'client_id' => $client->id,
                'job_name' => $this->jobName,
            ]);

        }
    }

}

