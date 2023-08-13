<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSystemConfigAPIRequest;
use App\Http\Requests\API\UpdateSystemConfigAPIRequest;
use App\Http\Resources\Mobile\SystemConfigResource;
use App\Models\SystemConfig;
use App\Repositories\SystemConfigRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class SystemConfigController
 * @package App\Http\Controllers\API
 */
class SystemConfigAPIController extends AppBaseController
{
    /** @var  SystemConfigRepository */
    private $systemConfigRepository;

    public function __construct(SystemConfigRepository $systemConfigRepo)
    {
        $this->systemConfigRepository = $systemConfigRepo;
    }

    /**
     * Display a listing of the SystemConfig.
     * GET|HEAD /systemConfigs
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $systemConfigs = $this->systemConfigRepository->all(
            ['code' => 'company_notice', 'status' => SystemConfig::ACTIVE]
        );

        return $this->sendResponse(SystemConfigResource::collection($systemConfigs), 'System Configs retrieved successfully');
    }

    /**
     * Store a newly created SystemConfig in storage.
     * POST /systemConfigs
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $systemConfig = $this->systemConfigRepository->create($input);

        return $this->sendResponse($systemConfig->toArray(), 'System Config saved successfully');
    }

    /**
     * Display the specified SystemConfig.
     * GET|HEAD /systemConfigs/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var SystemConfig $systemConfig */
        $systemConfig = $this->systemConfigRepository->find($id);

        if (empty($systemConfig)) {
            return $this->sendError('System Config not found');
        }

        return $this->sendResponse($systemConfig->toArray(), 'System Config retrieved successfully');
    }

    /**
     * Update the specified SystemConfig in storage.
     * PUT/PATCH /systemConfigs/{id}
     *
     * @param int $id
     * @param UpdateSystemConfigAPIRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var SystemConfig $systemConfig */
        $systemConfig = $this->systemConfigRepository->find($id);

        if (empty($systemConfig)) {
            return $this->sendError('System Config not found');
        }

        $systemConfig = $this->systemConfigRepository->update($input, $id);

        return $this->sendResponse($systemConfig->toArray(), 'SystemConfig updated successfully');
    }

    /**
     * Remove the specified SystemConfig from storage.
     * DELETE /systemConfigs/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var SystemConfig $systemConfig */
        $systemConfig = $this->systemConfigRepository->find($id);

        if (empty($systemConfig)) {
            return $this->sendError('System Config not found');
        }

        $systemConfig->delete();

        return $this->sendSuccess('System Config deleted successfully');
    }
}
