<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateTemplateAPIRequest;
use App\Http\Requests\API\UpdateTemplateAPIRequest;
use App\Http\Resources\TemplateResource;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TemplateController
 * @package App\Http\Controllers\API
 */
class TemplateAPIController extends AppBaseController
{
    /** @var  TemplateRepository */
    private $templateRepository;

    public function __construct(TemplateRepository $templateRepo)
    {
        $this->templateRepository = $templateRepo;
    }

    /**
     * Display a listing of the Template.
     * GET|HEAD /templates
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $templates = $this->templateRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->templateRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => TemplateResource::collection($templates),
            'total' => $total
        ], 'Templates retrieved successfully');
    }

    /**
     * Store a newly created Template in storage.
     * POST /templates
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $template = $this->templateRepository->create($input);

        return $this->sendResponse(TemplateResource::make($template), 'Template saved successfully');
    }

    /**
     * Display the specified Template.
     * GET|HEAD /templates/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Template $template */
        $template = $this->templateRepository->find($id);

        if (empty($template)) {
            return $this->sendError('Template not found');
        }

        return $this->sendResponse(TemplateResource::make($template), 'Template retrieved successfully');
    }

    /**
     * Update the specified Template in storage.
     * PUT/PATCH /templates/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Template $template */
        $template = $this->templateRepository->find($id);

        if (empty($template)) {
            return $this->sendError('Template not found');
        }

        $template = $this->templateRepository->update($input, $id);

        return $this->sendResponse(TemplateResource::make($template), 'Template updated successfully');
    }

    /**
     * Remove the specified Template from storage.
     * DELETE /templates/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Template $template */
        $template = $this->templateRepository->find($id);

        if (empty($template)) {
            return $this->sendError('Template not found');
        }

        $template->delete();

        return $this->sendSuccess('Template deleted successfully');
    }
}
