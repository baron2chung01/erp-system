<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSystemConfigAPIRequest;
use App\Http\Requests\API\UpdateSystemConfigAPIRequest;
use App\Http\Resources\SystemConfigResource;
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
        $this->authorize('company_notice_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $systemConfigs = $this->systemConfigRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->systemConfigRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => SystemConfigResource::collection($systemConfigs),
            'total' => $total,
        ], 'System Configs retrieved successfully');
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
        $this->authorize('company_notice_create');

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
        $this->authorize('company_notice_view');

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
        $this->authorize('company_notice_update');

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
        $this->authorize('company_notice_delete');

        /** @var SystemConfig $systemConfig */
        $systemConfig = $this->systemConfigRepository->find($id);

        if (empty($systemConfig)) {
            return $this->sendError('System Config not found');
        }

        $systemConfig->delete();

        return $this->sendSuccess('System Config deleted successfully');
    }
}
