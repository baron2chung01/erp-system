<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateWorkInstructionAPIRequest;
use App\Http\Requests\API\UpdateWorkInstructionAPIRequest;
use App\Http\Resources\WorkInstructionResource;
use App\Models\WorkInstruction;
use App\Repositories\WorkInstructionRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class WorkInstructionController
 * @package App\Http\Controllers\API
 */
class WorkInstructionAPIController extends AppBaseController
{
    /** @var  WorkInstructionRepository */
    private $workInstructionRepository;

    public function __construct(WorkInstructionRepository $workInstructionRepo)
    {
        $this->workInstructionRepository = $workInstructionRepo;
    }

    /**
     * Display a listing of the WorkInstruction.
     * GET|HEAD /workInstructions
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('work_instruction_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->workInstructionRepository->all([
                'status' => WorkInstruction::ACTIVE,
            ])->pluck('display_name', 'id'));
        }

        if (isset($input['search'])) {
            $query = WorkInstruction::where('work_instruction_no', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('name', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $workInstructions = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $workInstructions = $this->workInstructionRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->workInstructionRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => WorkInstructionResource::collection($workInstructions),
            'total' => $total,
        ], 'Work Instructions retrieved successfully');
    }

    /**
     * Store a newly created WorkInstruction in storage.
     * POST /workInstructions
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('work_instruction_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $workInstruction = $this->workInstructionRepository->create($input);

        return $this->sendResponse(WorkInstructionResource::make($workInstruction), 'Work Instruction saved successfully');
    }

    /**
     * Display the specified WorkInstruction.
     * GET|HEAD /workInstructions/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('work_instruction_view');

        /** @var WorkInstruction $workInstruction */
        $workInstruction = $this->workInstructionRepository->find($id);

        if (empty($workInstruction)) {
            return $this->sendError('Work Instruction not found');
        }

        return $this->sendResponse(WorkInstructionResource::make($workInstruction), 'Work Instruction retrieved successfully');
    }

    /**
     * Update the specified WorkInstruction in storage.
     * PUT/PATCH /workInstructions/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('work_instruction_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var WorkInstruction $workInstruction */
        $workInstruction = $this->workInstructionRepository->find($id);

        if (empty($workInstruction)) {
            return $this->sendError('Work Instruction not found');
        }

        $workInstruction = $this->workInstructionRepository->update($input, $id);

        return $this->sendResponse(WorkInstructionResource::make($workInstruction), 'WorkInstruction updated successfully');
    }

    /**
     * Remove the specified WorkInstruction from storage.
     * DELETE /workInstructions/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('work_instruction_delete');

        /** @var WorkInstruction $workInstruction */
        $workInstruction = $this->workInstructionRepository->find($id);

        if (empty($workInstruction)) {
            return $this->sendError('Work Instruction not found');
        }

        $workInstruction->delete();

        return $this->sendSuccess('Work Instruction deleted successfully');
    }
}
