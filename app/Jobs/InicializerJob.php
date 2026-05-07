<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\{
    InteractsWithQueue,
    SerializesModels
};

use App\Jobs\Middleware\{
    LogApiInitializer,
    IdempotencyVerification
};

use App\Enums\LogApiStatusEnum;
use App\Models\LogsApi;

/**
 * Classe abstrata responsável por enviar para a fila
 * e processar jobs com log e idempotência automaticamente
 */
abstract class InicializerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O número de vezes que o job pode ser tentado.
     * @var int
     */
    public $tries = 3;

    /**
     * O número de segundos a aguardar antes de tentar novamente.
     * Pode ser um único inteiro ou um array para backoff exponencial.
     * @var int|array
     */
    public $backoff = 10;

    /**
     * O número de segundos que o job pode ser executado antes de expirar.
     * @var int
     */
    public $timeout = 120;

    // Id do LogsApi
    public ?int $logApiId = null;

    // Instância do LogsApi
    public ?LogsApi $logApi = null;

    /**
     * Responsável por receber os parâmetros do job
     */
    public function __construct(?int $logApiId = null)
    {
        $this->logApiId = $logApiId;
    }

    /**
     * Método principal executado pelo Laravel Queue.
     *
     * Responsável por centralizar o fluxo de execução do Job,
     * garantindo o processamento da lógica principal através
     * do método process() e o controle do LogApi.
     *
     * - Em caso de sucesso: marca o log como PROCESSED
     * - Em caso de erro: marca o log como FAILED e relança a exceção
     *
     * Isso evita duplicação de código e garante um padrão único
     * de execução para todos os Jobs que estendem esta classe.
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function handle()
    {
        /**
         * Tenta executar o processamento
         */
        try {

            // Executa o processamento
            $result = $this->process();

            // Finaliza o log como processado
            $this->finishLog(LogApiStatusEnum::PROCESSED);

            // Retorna o resultado
            return $result;

        } catch (\Throwable $e) {

            // Finaliza o log como falhado
            $this->finishLog(LogApiStatusEnum::FAILED);

            // Lança a exceção para o Laravel tratar
            throw $e;
        }
    }

    /**
     * Método responsável por executar o processamento
     */
    abstract protected function process(): mixed;

    /**
     * Middlewares que rodam antes do handle
     */
    public function middleware()
    {
        return [
            new LogApiInitializer(),
            new IdempotencyVerification(),
        ];
    }

    /**
     * Inicializa o log APÓS conexão com tenant
     */
    public function initializeLogApi(): void
    {
        if ($this->logApiId) {
            $this->logApi = LogsApi::find($this->logApiId);
        }
    }

    /**
     * Finaliza o Log de acordo com
     * o resultado do Job
     */
    protected function finishLog(LogApiStatusEnum $status): void
    {
        if ($this->logApi) {
            $this->logApi->status = $status->value;
            $this->logApi->save();
        }
    }
}
