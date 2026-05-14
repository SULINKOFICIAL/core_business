<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Throwable;

class ProvisioningIntegrityService
{
    /**
     * Executa as validações necessárias para criar novos tenants.
     */
    public function check(): array
    {
        $checks = [
            $this->checkEnvironment(),
            $this->checkPrivateKey(),
            $this->checkCpanelDomainsApi(),
            $this->checkTemplateDatabase(),
            $this->checkSshConnection(),
        ];

        return [
            'ready' => collect($checks)->every(fn ($check) => $check['status'] === 'success'),
            'checks' => $checks,
            'summary' => $this->buildSummary($checks),
        ];
    }

    private function checkEnvironment(): array
    {
        /**
         * Esses valores são os mesmos usados no fluxo real de criação:
         * API do cPanel, clonagem via SSH e autenticação Central/Tenant.
         */
        $requiredKeys = [
            'CPANEL_URL',
            'CPANEL_USER',
            'CPANEL_PASS',
            'CPANEL_PREFIX',
            'WHM_IP',
            'SSH_PASSPHRASE',
            'CENTRAL_TOKEN',
        ];

        $missingKeys = [];

        foreach ($requiredKeys as $key) {
            $value = env($key);

            if ($value === null || $value === '') {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            return $this->result(
                'environment',
                'Variáveis obrigatórias',
                'error',
                'Configuração incompleta no .env.',
                'Ausentes: ' . implode(', ', $missingKeys)
            );
        }

        return $this->result(
            'environment',
            'Variáveis obrigatórias',
            'success',
            'Todas as variáveis necessárias foram encontradas.',
            'CPANEL_URL, CPANEL_USER, CPANEL_PASS, CPANEL_PREFIX, WHM_IP, SSH_PASSPHRASE e CENTRAL_TOKEN estão preenchidas.'
        );
    }

    private function checkPrivateKey(): array
    {
        /**
         * A chave privada precisa estar disponível para o PHP, pois a etapa
         * de banco usa SSH para executar o dump do template no servidor.
         */
        $keyPath = storage_path('keys/id_rsa');

        if (!file_exists($keyPath)) {
            return $this->result(
                'private_key',
                'Chave privada SSH',
                'error',
                'Arquivo de chave não encontrado.',
                $keyPath
            );
        }

        if (!is_readable($keyPath)) {
            return $this->result(
                'private_key',
                'Chave privada SSH',
                'error',
                'Arquivo de chave encontrado, mas sem permissão de leitura.',
                $keyPath
            );
        }

        return $this->result(
            'private_key',
            'Chave privada SSH',
            'success',
            'Arquivo de chave encontrado e legível.',
            $keyPath
        );
    }

    private function checkCpanelDomainsApi(): array
    {
        try {
            /**
             * Este endpoint é leve e valida duas coisas críticas:
             * URL/credenciais do cPanel e permissão para consultar domínios.
             */
            $response = $this->requestCpanelApi('GET', '/execute/DomainInfo/list_domains');

            if (!$this->cpanelResponseSucceeded($response)) {
                return $this->result(
                    'cpanel_domains_api',
                    'API cPanel',
                    'error',
                    'A API respondeu, mas não confirmou sucesso.',
                    $this->extractCpanelError($response)
                );
            }

            return $this->result(
                'cpanel_domains_api',
                'API cPanel',
                'success',
                'Autenticação Basic e endpoint de domínios responderam corretamente.',
                'Endpoint: DomainInfo/list_domains'
            );
        } catch (Throwable $throwable) {
            return $this->result(
                'cpanel_domains_api',
                'API cPanel',
                'error',
                'Falha ao consultar a API do cPanel.',
                $throwable->getMessage()
            );
        }
    }

    private function checkTemplateDatabase(): array
    {
        $cpanelPrefix = env('CPANEL_PREFIX');

        if ($cpanelPrefix === null || $cpanelPrefix === '') {
            return $this->result(
                'template_database',
                'Banco template',
                'error',
                'CPANEL_PREFIX não está preenchido.',
                'Não foi possível montar o nome do banco template.'
            );
        }

        $templateDatabase = $cpanelPrefix . '_template';

        try {
            /**
             * O provisionamento clona esse banco com mysqldump.
             * Sem ele, a criação do tenant para na etapa database.
             */
            $response = $this->requestCpanelApi('GET', '/execute/Mysql/list_databases');

            if (!$this->cpanelResponseSucceeded($response)) {
                return $this->result(
                    'template_database',
                    'Banco template',
                    'error',
                    'A API de bancos respondeu, mas não confirmou sucesso.',
                    $this->extractCpanelError($response)
                );
            }

            if (!$this->responseContainsDatabase($response, $templateDatabase)) {
                return $this->result(
                    'template_database',
                    'Banco template',
                    'error',
                    'Banco template não encontrado na conta cPanel.',
                    'Esperado: ' . $templateDatabase
                );
            }

            return $this->result(
                'template_database',
                'Banco template',
                'success',
                'Banco template localizado na conta cPanel.',
                $templateDatabase
            );
        } catch (Throwable $throwable) {
            return $this->result(
                'template_database',
                'Banco template',
                'error',
                'Falha ao consultar os bancos do cPanel.',
                $throwable->getMessage()
            );
        }
    }

    private function checkSshConnection(): array
    {
        $whmIp = env('WHM_IP');
        $cpanelUser = env('CPANEL_USER');
        $sshPassphrase = env('SSH_PASSPHRASE');
        $keyPath = storage_path('keys/id_rsa');

        if ($whmIp === null || $whmIp === '' || $cpanelUser === null || $cpanelUser === '') {
            return $this->result(
                'ssh_connection',
                'Conexão SSH',
                'error',
                'WHM_IP ou CPANEL_USER não estão preenchidos.',
                'A clonagem do banco template depende dessa conexão.'
            );
        }

        if (!file_exists($keyPath) || !is_readable($keyPath)) {
            return $this->result(
                'ssh_connection',
                'Conexão SSH',
                'error',
                'Chave privada indisponível para autenticação SSH.',
                $keyPath
            );
        }

        try {
            /**
             * Usa o mesmo mecanismo do provisionamento real:
             * chave privada local, passphrase do .env e usuário do cPanel.
             */
            $ssh = new SSH2($whmIp);
            $privateKey = PublicKeyLoader::loadPrivateKey(file_get_contents($keyPath), $sshPassphrase);

            if (!$ssh->login($cpanelUser, $privateKey)) {
                return $this->result(
                    'ssh_connection',
                    'Conexão SSH',
                    'error',
                    'Falha ao autenticar via SSH com a chave privada.',
                    'Verifique CPANEL_USER, WHM_IP, SSH_PASSPHRASE e a chave storage/keys/id_rsa.'
                );
            }

            return $this->result(
                'ssh_connection',
                'Conexão SSH',
                'success',
                'Login SSH realizado com sucesso.',
                'A etapa de clonagem do banco pode executar comandos no servidor.'
            );
        } catch (Throwable $throwable) {
            return $this->result(
                'ssh_connection',
                'Conexão SSH',
                'error',
                'Falha ao validar conexão SSH.',
                $throwable->getMessage()
            );
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function requestCpanelApi(string $method, string $endpoint): array
    {
        $cpanelUrl = env('CPANEL_URL');
        $cpanelUser = env('CPANEL_USER');
        $cpanelPass = env('CPANEL_PASS');

        if ($cpanelUrl === null || $cpanelUrl === '') {
            throw new Exception('CPANEL_URL não está preenchido.');
        }

        if ($cpanelUser === null || $cpanelUser === '' || $cpanelPass === null || $cpanelPass === '') {
            throw new Exception('CPANEL_USER ou CPANEL_PASS não estão preenchidos.');
        }

        $guzzle = new Guzzle([
            'timeout' => 12,
            'connect_timeout' => 5,
        ]);

        /**
         * Mantém o mesmo tipo de autenticação usado no CpanelProvisioningService:
         * Basic Auth com usuário e senha da conta cPanel.
         */
        $response = $guzzle->request($method, $cpanelUrl . $endpoint, [
            'auth' => [$cpanelUser, $cpanelPass],
        ]);

        $payload = json_decode($response->getBody()->getContents(), true);

        if (!is_array($payload)) {
            throw new Exception('Resposta do cPanel não retornou JSON válido.');
        }

        return $payload;
    }

    private function cpanelResponseSucceeded(array $response): bool
    {
        /**
         * Alguns endpoints retornam status no topo, outros dentro de result.
         * Aceitamos os dois formatos sem depender de helpers dinâmicos.
         */
        $status = $response['status'] ?? null;

        if ($status === 1 || $status === '1') {
            return true;
        }

        $resultStatus = null;

        if (isset($response['result']) && is_array($response['result'])) {
            $resultStatus = $response['result']['status'] ?? null;
        }

        if ($resultStatus === 1 || $resultStatus === '1') {
            return true;
        }

        return false;
    }

    private function responseContainsDatabase(array $response, string $database): bool
    {
        /**
         * O cPanel pode devolver a lista em data ou result.data,
         * dependendo da versão e do endpoint UAPI chamado.
         */
        $items = $this->getCpanelDataItems($response);

        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['database'] ?? null) === $database) {
                return true;
            }

            if (($item['name'] ?? null) === $database) {
                return true;
            }
        }

        return false;
    }

    private function extractCpanelError(array $response): string
    {
        $errors = $response['errors'] ?? null;

        if (is_array($errors) && !empty($errors)) {
            return implode(' | ', $errors);
        }

        $resultErrors = null;

        if (isset($response['result']) && is_array($response['result'])) {
            $resultErrors = $response['result']['errors'] ?? null;
        }

        if (is_array($resultErrors) && !empty($resultErrors)) {
            return implode(' | ', $resultErrors);
        }

        $messages = $response['messages'] ?? null;

        if (is_array($messages) && !empty($messages)) {
            return implode(' | ', $messages);
        }

        $resultMessage = null;

        if (isset($response['result']) && is_array($response['result'])) {
            $resultMessage = $response['result']['message'] ?? null;
        }

        if ($resultMessage !== null && $resultMessage !== '') {
            return $resultMessage;
        }

        return 'Sem detalhe retornado pelo cPanel.';
    }

    private function getCpanelDataItems(array $response): array
    {
        if (isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        }

        if (
            isset($response['result'])
            && is_array($response['result'])
            && isset($response['result']['data'])
            && is_array($response['result']['data'])
        ) {
            return $response['result']['data'];
        }

        return [];
    }

    private function buildSummary(array $checks): array
    {
        $successCount = collect($checks)->where('status', 'success')->count();
        $errorCount = collect($checks)->where('status', 'error')->count();

        return [
            'success' => $successCount,
            'error' => $errorCount,
            'total' => count($checks),
        ];
    }

    private function result(string $key, string $label, string $status, string $message, ?string $detail = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'detail' => $detail,
        ];
    }
}
