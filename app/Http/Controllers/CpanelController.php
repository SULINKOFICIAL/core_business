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
        $result = $this->cpanelProvisioningService->createSubdomainForClient((int) $id);

        return response()->json($result);
    }

    public function clientMakeDatabase($id)
    {
        $result = $this->cpanelProvisioningService->cloneDatabaseForClient((int) $id);

        return response()->json($result);
    }

    public function clientAddTokenAndUser($id)
    {
        $result = $this->cpanelProvisioningService->addTokenAndUserForClient((int) $id);

        return response()->json($result);
    }
}
