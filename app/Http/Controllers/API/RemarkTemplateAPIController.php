<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateRemarkTemplateAPIRequest;
use App\Http\Requests\API\UpdateRemarkTemplateAPIRequest;
use App\Http\Resources\RemarkTemplateResource;
use App\Models\RemarkTemplate;
use App\Repositories\RemarkTemplateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RemarkTemplateAPIController
 */
class RemarkTemplateAPIController extends AppBaseController
{
    private RemarkTemplateRepository $remarkTemplateRepository;

    public function __construct(RemarkTemplateRepository $remarkTemplateRepo)
    {
        $this->remarkTemplateRepository = $remarkTemplateRepo;
    }

    /**
     * Display a listing of the RemarkTemplates.
     * GET|HEAD /remark-templates
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('purchase_order_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->remarkTemplateRepository->all([
                'status' => RemarkTemplate::ACTIVE,
            ]));
        }

        if (isset($input['search'])) {
            $query = RemarkTemplate::where('title', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $remarkTemplates = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $remarkTemplates = $this->remarkTemplateRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->remarkTemplateRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => RemarkTemplateResource::collection($remarkTemplates),
            'total' => $total,
        ], 'Work Instructions retrieved successfully');

    }

    /**
     * Store a newly created RemarkTemplate in storage.
     * POST /remark-templates
     */
    public function store(CreateRemarkTemplateAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $remarkTemplate = $this->remarkTemplateRepository->create($input);

        return $this->sendResponse(RemarkTemplateResource::make($remarkTemplate), 'Remark Template saved successfully');
    }

    /**
     * Display the specified RemarkTemplate.
     * GET|HEAD /remark-templates/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var RemarkTemplate $remarkTemplate */
        $remarkTemplate = $this->remarkTemplateRepository->find($id);

        if (empty($remarkTemplate)) {
            return $this->sendError('Remark Template not found');
        }

        return $this->sendResponse(RemarkTemplateResource::make($remarkTemplate), 'Remark Template retrieved successfully');
    }

    /**
     * Update the specified RemarkTemplate in storage.
     * PUT/PATCH /remark-templates/{id}
     */
    public function update($id, UpdateRemarkTemplateAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var RemarkTemplate $remarkTemplate */
        $remarkTemplate = $this->remarkTemplateRepository->find($id);

        if (empty($remarkTemplate)) {
            return $this->sendError('Remark Template not found');
        }

        $remarkTemplate = $this->remarkTemplateRepository->update($input, $id);

        return $this->sendResponse(RemarkTemplateResource::make($remarkTemplate), 'RemarkTemplate updated successfully');
    }

    /**
     * Remove the specified RemarkTemplate from storage.
     * DELETE /remark-templates/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var RemarkTemplate $remarkTemplate */
        $remarkTemplate = $this->remarkTemplateRepository->find($id);

        if (empty($remarkTemplate)) {
            return $this->sendError('Remark Template not found');
        }

        $remarkTemplate->delete();

        return $this->sendSuccess('Remark Template deleted successfully');
    }
}
