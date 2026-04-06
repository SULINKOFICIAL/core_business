<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\CheckOnboardingIdentityRequest;
use App\Http\Requests\Api\FinalizeOnboardingRequest;
use App\Http\Requests\Api\SaveOnboardingStepRequest;
use App\Models\Tenant;
use App\Models\TenantProvisioning;
use App\Services\CpanelProvisioningService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApisOnboardingController extends Controller
{
    public function __construct(
        private readonly Tenant $tenantRepository,
        private readonly CpanelProvisioningService $cpanelProvisioningService
    ) {}

    /**
     * Verifica se já existe tenant para identidade informada no onboarding.
     * Permite continuidade apenas quando o cadastro ainda não foi concluído.
     */
    public function checkOnboardingIdentity(CheckOnboardingIdentityRequest $request): JsonResponse
    {
        $data = $request->validated();

        // A consulta prioriza tenant concluído para nunca liberar continuidade indevida.
        $tenant = $this->findTenantByIdentity($data);
        if (!$tenant) {
            return response()->json([
                'exists' => false,
                'is_completed' => false,
                'can_continue' => true,
            ]);
        }

        $isCompleted = !empty($tenant->onboarding_completed_at);

        return response()->json([
            'exists' => true,
            'is_completed' => $isCompleted,
            'can_continue' => !$isCompleted,
        ]);
    }

    /**
     * Persiste incrementalmente os dados da etapa atual do onboarding.
     * Cria rascunho quando não houver tenant correspondente.
     */
    public function saveOnboardingStep(SaveOnboardingStepRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $step = $validated['step'];
        $data = $this->mapOnboardingPayload($request->all());

        // Primeiro tentamos tenant_id recebido; sem ele, resolvemos por identidade.
        $tenant = $this->resolveOnboardingTenant($data);

        if ($tenant && !empty($tenant->onboarding_completed_at)) {
            return response()->json([
                'message' => 'Cadastro já finalizado para este tenant.',
            ], 409);
        }

        $updatableData = $this->extractOnboardingUpdatableData($data);
        $updatableData['onboarding_current_step'] = $step;

        if ($tenant) {
            // Garante data de início somente no primeiro salvamento incremental.
            if (empty($tenant->onboarding_started_at)) {
                $updatableData['onboarding_started_at'] = now();
            }

            $tenant->update($updatableData);
        } else {
            // Criação de rascunho: permite retomada sem provisionar infraestrutura.
            $tenant = $this->tenantRepository->create(array_merge($this->buildDraftTenantDefaults($data), $updatableData, [
                'onboarding_started_at' => now(),
            ]));
        }

        return response()->json([
            'tenant_id' => $tenant->id,
            'step' => $tenant->onboarding_current_step,
            'completed' => !empty($tenant->onboarding_completed_at),
        ]);
    }

    /**
     * Finaliza o onboarding, executa provisionamento e conclui o cadastro.
     * Mantém bloqueio para identidades já finalizadas.
     */
    public function finalizeOnboarding(FinalizeOnboardingRequest $request): JsonResponse
    {
        $request->validated();

        $data = $this->mapOnboardingPayload($request->all());

        // A finalização também aceita tenant_id ausente e resolve por identidade.
        $tenant = $this->resolveOnboardingTenant($data);
        if (!$tenant) {
            return response()->json([
                'message' => 'Não foi possível localizar o tenant para finalização.',
            ], 404);
        }

        if (!empty($tenant->onboarding_completed_at)) {
            return response()->json([
                'message' => 'Cadastro já finalizado para este tenant.',
            ], 409);
        }

        // Consolidamos dados da última etapa antes de qualquer provisionamento.
        $this->updateTenantFinalStepData($tenant, $data);

        // Não permite finalizar se outro tenant já concluído usar a mesma identidade.
        $this->assertNoCompletedConflicts($tenant);

        $this->replaceTenantMainGoals($tenant, $data['main_goals'] ?? []);

        $provisioningData = $this->buildProvisioningData($tenant, $data, $request);
        $this->applyProvisioningData($tenant, $provisioningData);

        $assignRequest = new Request(['package_id' => 1]);
        app(PackageController::class)->assign($assignRequest, $tenant->id);

        $provisioningResult = $this->cpanelProvisioningService->runProvisioning($tenant);
        $tenant->onboarding_completed_at = now();
        $tenant->onboarding_current_step = 'address';
        $tenant->save();

        return response()->json([
            'tenant_id' => $tenant->id,
            'message' => 'Onboarding finalizado com sucesso.',
            'provisioning' => $provisioningResult,
        ]);
    }

    /**
     * Normaliza o payload do onboarding para o formato interno da central.
     * Preserva compatibilidade com chaves antigas do frontend.
     */
    private function mapOnboardingPayload(array $data): array
    {
        $allowedMainGoals = [
            'centralizar_atendimentos',
            'vender_online',
            'controlar_estoque',
            'vender_servicos',
        ];

        if (isset($data['main_goals']) && !is_array($data['main_goals'])) {
            $data['main_goals'] = [$data['main_goals']];
        }

        $data['main_goals'] = array_values(array_unique(array_filter(
            $data['main_goals'] ?? [],
            fn ($goal) => in_array($goal, $allowedMainGoals, true)
        )));

        $data['tips_whatsapp'] = !empty($data['tips_whatsapp']);
        $data['tips_email'] = !empty($data['tips_email']);
        $data['has_coupon'] = !empty($data['has_coupon']);

        if (!empty($data['company_zip_code'])) {
            $data['company_zip_code'] = onlyNumbers($data['company_zip_code']);
        }

        return $data;
    }

    /**
     * Busca tenant por email, CNPJ ou CPF priorizando cadastro finalizado.
     * Evita liberar continuidade quando já existir tenant concluído.
     */
    private function findTenantByIdentity(array $data): ?Tenant
    {
        $baseQuery = fn () => Tenant::query()
            ->orderByRaw('onboarding_completed_at IS NOT NULL DESC')
            ->orderByDesc('id');

        $email = !empty($data['email']) ? mb_strtolower(trim((string) $data['email'])) : null;
        $cnpj = !empty($data['cnpj']) ? onlyNumbers((string) $data['cnpj']) : null;
        $cpf = !empty($data['cpf']) ? onlyNumbers((string) $data['cpf']) : null;

        if ($email) {
            // Email é prioridade de busca por ser o identificador mais estável no fluxo.
            $tenant = $baseQuery()->whereRaw('LOWER(email) = ?', [$email])->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if ($cnpj) {
            $tenant = $baseQuery()->where('cnpj', $cnpj)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if ($cpf) {
            $tenant = $baseQuery()->where('cpf', $cpf)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Resolve tenant alvo do onboarding via tenant_id ou identidade.
     * Mantém um único ponto de decisão para criação/atualização.
     */
    private function resolveOnboardingTenant(array $data): ?Tenant
    {
        if (!empty($data['tenant_id'])) {
            $tenant = Tenant::find($data['tenant_id']);
            if ($tenant) {
                // Quando tenant_id existe, ele prevalece sobre busca por identidade.
                return $tenant;
            }
        }

        return $this->findTenantByIdentity($data);
    }

    /**
     * Filtra campos de onboarding permitidos para atualização do tenant.
     * Centraliza saneamento de email, documentos e telefone.
     */
    private function extractOnboardingUpdatableData(array $data): array
    {
        $allowedFields = [
            'name',
            'email',
            'whatsapp',
            'company',
            'cnpj',
            'cpf',
            'company_profile',
            'company_zip_code',
            'company_city_state',
            'company_address',
            'company_neighborhood',
            'company_number',
            'company_complement',
            'tips_whatsapp',
            'tips_email',
            'has_coupon',
            'coupon_code',
            'document_type',
        ];

        $payload = array_intersect_key($data, array_flip($allowedFields));

        // Normalização evita divergência de comparação entre ambientes.
        if (!empty($payload['email'])) {
            $payload['email'] = mb_strtolower(trim((string) $payload['email']));
        }
        if (!empty($payload['whatsapp'])) {
            $payload['whatsapp'] = onlyNumbers((string) $payload['whatsapp']);
        }
        if (!empty($payload['cnpj'])) {
            $payload['cnpj'] = onlyNumbers((string) $payload['cnpj']);
        }
        if (!empty($payload['cpf'])) {
            $payload['cpf'] = onlyNumbers((string) $payload['cpf']);
        }
        if (array_key_exists('company_zip_code', $payload) && !empty($payload['company_zip_code'])) {
            $payload['company_zip_code'] = onlyNumbers((string) $payload['company_zip_code']);
        }

        return $payload;
    }

    /**
     * Define valores mínimos para criação de tenant em rascunho.
     * Gera domínio temporário e token inicial para o cadastro.
     */
    private function buildDraftTenantDefaults(array $data): array
    {
        $name = (string) ($data['name'] ?? 'Cadastro em andamento');
        $company = (string) ($data['company'] ?? $name ?: 'Cadastro em andamento');
        $draftSlug = Str::slug($company ?: $name);
        if ($draftSlug === '') {
            // Fallback quando nome/empresa vier vazio ou com caracteres inválidos.
            $draftSlug = 'tenant-' . Str::lower(Str::random(8));
        }

        return [
            'name' => $name,
            'company' => $company,
            'domain' => "draft-{$draftSlug}-" . time() . '.micore.com.br',
            'created_by' => 1,
            'status' => true,
            'token' => hash('sha256', $name . microtime(true)),
        ];
    }

    /**
     * Garante que não exista outro tenant concluído com mesma identidade.
     * Bloqueia finalização para evitar duplicidade de conta ativa.
     */
    private function assertNoCompletedConflicts(Tenant $tenant): void
    {
        $conflictQuery = Tenant::whereNotNull('onboarding_completed_at')
            ->where('id', '!=', $tenant->id);

        $conflictQuery->where(function ($query) use ($tenant) {
            if (!empty($tenant->email)) {
                $query->orWhereRaw('LOWER(email) = ?', [mb_strtolower((string) $tenant->email)]);
            }
            if (!empty($tenant->cnpj)) {
                $query->orWhere('cnpj', onlyNumbers((string) $tenant->cnpj));
            }
            if (!empty($tenant->cpf)) {
                $query->orWhere('cpf', onlyNumbers((string) $tenant->cpf));
            }
        });

        if ($conflictQuery->exists()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Já existe uma conta finalizada com esses dados.',
            ], 409));
        }
    }

    /**
     * Atualiza os dados consolidados da etapa final no tenant rascunho.
     * Mantém a etapa atual marcada como address antes de provisionar.
     */
    private function updateTenantFinalStepData(Tenant $tenant, array $data): void
    {
        $updatableData = $this->extractOnboardingUpdatableData($data);
        $updatableData['onboarding_current_step'] = 'address';
        if (empty($tenant->onboarding_started_at)) {
            $updatableData['onboarding_started_at'] = now();
        }

        $tenant->update($updatableData);
    }

    /**
     * Substitui os objetivos principais do tenant pelos selecionados no fluxo.
     * Garante persistência relacional consistente por finalização.
     */
    private function replaceTenantMainGoals(Tenant $tenant, array $mainGoals): void
    {
        // O conjunto é substituído por completo para evitar objetivos obsoletos.
        $tenant->mainGoals()->delete();
        if (empty($mainGoals)) {
            return;
        }

        $tenant->mainGoals()->createMany(array_map(function ($goal) {
            return ['goal' => $goal];
        }, $mainGoals));
    }

    /**
     * Monta o payload de provisionamento técnico e primeiro usuário.
     * Reaproveita dados já coletados no onboarding para instalação.
     */
    private function buildProvisioningData(Tenant $tenant, array $data, Request $request): array
    {
        $tenantCompany = $tenant->company ?: $tenant->name;
        $rawDomain = verifyIfAllow($tenantCompany ?: ('tenant-' . $tenant->id));
        $tableUser = str_replace('-', '_', $rawDomain);
        $tableName = env('CPANEL_PREFIX') . '_' . $tableUser;
        $firstUserName = $tenant->name ?: 'Usuário';
        $plainPassword = (string) ($data['password'] ?? $request->input('password', ''));
        // Senha do primeiro usuário pode vir de etapa salva ou do payload final.

        return [
            'domain' => $rawDomain . '.micore.com.br',
            'token' => hash('sha256', ($tenantCompany ?: $firstUserName) . microtime(true)),
            'provisioning' => [
                'table' => $tableName,
                'table_user' => $tableUser,
                'table_password' => Str::random(12),
                'first_user' => [
                    'name' => $firstUserName,
                    'email' => $tenant->email,
                    'password' => $plainPassword,
                    'short_name' => generateShortName($firstUserName),
                ],
                'install' => TenantProvisioning::STEP_SUBDOMAIN,
            ],
        ];
    }

    /**
     * Persiste domínio/token e dados de provisionamento do tenant.
     * Cria runtime status quando ainda não existir registro técnico.
     */
    private function applyProvisioningData(Tenant $tenant, array $provisioningData): void
    {
        if (empty($tenant->token)) {
            // Mantém token existente quando já houver integração ativa.
            $tenant->token = $provisioningData['token'];
        }

        $tenant->domain = $provisioningData['domain'];
        $tenant->save();

        if ($tenant->provisioning) {
            $tenant->provisioning()->update($provisioningData['provisioning']);
        } else {
            $tenant->provisioning()->create($provisioningData['provisioning']);
        }

        if (!$tenant->runtimeStatus) {
            $tenant->runtimeStatus()->create();
        }
    }
}
