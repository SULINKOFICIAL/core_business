<?php

namespace App\Http\Controllers;

use App\Services\CpanelProvisioningService;

class CpanelController extends Controller
{
    public function __construct(private CpanelProvisioningService $cpanelProvisioningService)
    {
    }

    public function make($id)
    {
        $result = $this->cpanelProvisioningService->runProvisioning((int) $id);

        return response()->json($result);
    }

    public function clientMakeDomain($id)
    {
        $result = $this->cpanelProvisioningService->createSubdomainForTenant((int) $id);

        return response()->json($result);
    }

    public function clientMakeDatabase($id)
    {
        $result = $this->cpanelProvisioningService->cloneDatabaseForTenant((int) $id);

        return response()->json($result);
    }

    public function clientAddTokenAndUser($id)
    {
        $result = $this->cpanelProvisioningService->addTokenAndUserForTenant((int) $id);

        return response()->json($result);
    }
}
