<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client as Guzzle;
use Throwable;

class CpanelDomainLookupService
{
    /**
     * Consulta um domínio no cPanel sem criar ou alterar recursos.
     */
    public function find(string $domainInput): array
    {
        $domain = $this->normalizeDomain($domainInput);

        if ($domain === '') {
            return [
                'searched' => $domainInput,
                'domain' => null,
                'exists' => false,
                'source' => null,
                'details' => null,
                'error' => 'Informe um domínio válido para consultar.',
            ];
        }

        try {
            /**
             * domains_data retorna metadados úteis para operação:
             * tipo, document root, status, IP e aliases.
             */
            $details = $this->findInDomainsData($domain);

            if ($details !== null) {
                return [
                    'searched' => $domainInput,
                    'domain' => $domain,
                    'exists' => true,
                    'source' => 'DomainInfo/domains_data',
                    'details' => $details,
                    'error' => null,
                ];
            }

            /**
             * list_domains é usado como fallback porque é o endpoint
             * mais simples para confirmar existência na conta.
             */
            if ($this->existsInListDomains($domain)) {
                return [
                    'searched' => $domainInput,
                    'domain' => $domain,
                    'exists' => true,
                    'source' => 'DomainInfo/list_domains',
                    'details' => [
                        'domain' => $domain,
                        'type' => null,
                        'documentroot' => null,
                        'status' => null,
                        'serveralias' => null,
                        'ip' => null,
                    ],
                    'error' => null,
                ];
            }

            return [
                'searched' => $domainInput,
                'domain' => $domain,
                'exists' => false,
                'source' => 'cPanel UAPI',
                'details' => null,
                'error' => null,
            ];
        } catch (Throwable $throwable) {
            return [
                'searched' => $domainInput,
                'domain' => $domain,
                'exists' => false,
                'source' => 'cPanel UAPI',
                'details' => null,
                'error' => $throwable->getMessage(),
            ];
        }
    }

    private function findInDomainsData(string $domain): ?array
    {
        $response = $this->requestCpanelApi('/execute/DomainInfo/domains_data');
        $items = $this->getCpanelDataItems($response);
        $domains = $this->flattenDomainItems($items);

        foreach ($domains as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['domain'] ?? null) !== $domain) {
                continue;
            }

            return [
                'domain' => $item['domain'] ?? $domain,
                'type' => $item['type'] ?? null,
                'documentroot' => $item['documentroot'] ?? null,
                'status' => $item['status'] ?? null,
                'serveralias' => $item['serveralias'] ?? null,
                'ip' => $item['ip'] ?? null,
            ];
        }

        return null;
    }

    private function existsInListDomains(string $domain): bool
    {
        $response = $this->requestCpanelApi('/execute/DomainInfo/list_domains');
        $items = $this->getCpanelDataItems($response);

        foreach ($items as $item) {
            if (is_array($item) && in_array($domain, $item, true)) {
                return true;
            }

            if ($item === $domain) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function requestCpanelApi(string $endpoint): array
    {
        $cpanelUrl = env('CPANEL_URL') ?? '';
        $cpanelUser = env('CPANEL_USER') ?? '';
        $cpanelPass = env('CPANEL_PASS') ?? '';

        if ($cpanelUrl === '' || $cpanelUser === '' || $cpanelPass === '') {
            throw new Exception('CPANEL_URL, CPANEL_USER ou CPANEL_PASS não estão preenchidos.');
        }

        $guzzle = new Guzzle([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        /**
         * Usa a mesma autenticação Basic do provisionamento atual.
         */
        $response = $guzzle->get($cpanelUrl . $endpoint, [
            'auth' => [$cpanelUser, $cpanelPass],
        ]);

        $payload = json_decode($response->getBody()->getContents(), true);

        if (!is_array($payload)) {
            throw new Exception('Resposta do cPanel não retornou JSON válido.');
        }

        if (!$this->cpanelResponseSucceeded($payload)) {
            throw new Exception('cPanel não confirmou sucesso na consulta.');
        }

        return $payload;
    }

    private function normalizeDomain(string $domainInput): string
    {
        $domain = preg_replace('/\s+/', '', $domainInput) ?? '';

        if (str_contains($domain, '://')) {
            $host = parse_url($domain, PHP_URL_HOST);
            $domain = is_string($host) ? $host : '';
        }

        if (str_contains($domain, '/')) {
            $parts = explode('/', $domain);
            $domain = $parts[0] ?? '';
        }

        if (str_contains($domain, ':')) {
            $parts = explode(':', $domain);
            $domain = $parts[0] ?? '';
        }

        $domain = mb_strtolower($domain);
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;

        return $domain;
    }

    private function cpanelResponseSucceeded(array $response): bool
    {
        $status = $response['status'] ?? null;

        if ($status === 1 || $status === '1') {
            return true;
        }

        if (isset($response['result']) && is_array($response['result'])) {
            $resultStatus = $response['result']['status'] ?? null;

            if ($resultStatus === 1 || $resultStatus === '1') {
                return true;
            }
        }

        return false;
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

    private function flattenDomainItems(array $items): array
    {
        $domains = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['domain'])) {
                $domains[] = $item;
                continue;
            }

            foreach ($item as $nestedItem) {
                if (is_array($nestedItem) && isset($nestedItem['domain'])) {
                    $domains[] = $nestedItem;
                }
            }
        }

        return $domains;
    }
}
