<?php

namespace App\Http\Controllers\Api;

use App\Application\Organizations\Contracts\ConnectOrganizationInterface;
use App\Application\Organizations\Contracts\GetCurrentOrganizationInterface;
use App\Application\Organizations\Contracts\ReparseOrganizationInterface;
use App\Application\Organizations\Exceptions\OrganizationAlreadyConnectedException;
use App\Application\Organizations\Exceptions\OrganizationNotConnectedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrganizationController extends Controller
{
    public function __construct(
        private readonly GetCurrentOrganizationInterface $getCurrentOrganization,
        private readonly ConnectOrganizationInterface $connectOrganization,
        private readonly ReparseOrganizationInterface $reparseOrganization,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $organization = $this->getCurrentOrganization->execute($request->user()->id);

        return response()->json([
            'organization' => $organization ? new OrganizationResource($organization) : null,
        ]);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        try {
            $organization = $this->connectOrganization->execute(
                $request->user()->id,
                $request->validated('url'),
            );
        } catch (OrganizationAlreadyConnectedException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'organization' => new OrganizationResource($organization),
        ], 202);
    }

    public function reparse(Request $request): JsonResponse
    {
        try {
            $organization = $this->reparseOrganization->execute($request->user()->id);
        } catch (OrganizationNotConnectedException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'organization' => new OrganizationResource($organization),
        ], 202);
    }
}
