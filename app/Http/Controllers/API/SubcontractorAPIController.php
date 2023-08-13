<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSubcontractorAPIRequest;
use App\Http\Requests\API\UpdateSubcontractorAPIRequest;
use App\Http\Resources\SubcontractorResource;
use App\Models\Subcontractor;
use App\Models\SubcontractorHasTask;
use App\Models\SubcontractorTaskPrice;
use App\Models\Task;
use App\Repositories\SubcontractorRepository;
use App\Traits\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SubcontractorAPIController
 */
class SubcontractorAPIController extends AppBaseController
{
    private SubcontractorRepository $subcontractorRepository;

    public function __construct(SubcontractorRepository $subcontractorRepo)
    {
        $this->subcontractorRepository = $subcontractorRepo;
    }

    /**
     * Display a listing of the Subcontractors.
     * GET|HEAD /subcontractors
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('supplier_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        if (empty($request->all())) {
            return response()->json($this->subcontractorRepository->all([
                'status' => Subcontractor::ACTIVE,
            ])->pluck('name', 'id'));
        }

        if (isset($input['search'])) {
            $query = Subcontractor::where('name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('email', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('address', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('phone', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('contact_person', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('payment_term', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('delivery_mode', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $roles = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $subcontractors = $this->subcontractorRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->subcontractorRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => SubcontractorResource::collection($subcontractors),
            'total' => $total,
        ], 'Subcontractors retrieved successfully');

    }

    /**
     * Store a newly created Subcontractor in storage.
     * POST /subcontractors
     */
    public function store(CreateSubcontractorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $subcontractor = $this->subcontractorRepository->create($input);

        return $this->sendResponse($subcontractor->toArray(), 'Subcontractor saved successfully');
    }

    /**
     * Display the specified Subcontractor.
     * GET|HEAD /subcontractors/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Subcontractor $subcontractor */
        $subcontractor = $this->subcontractorRepository->find($id);

        if (empty($subcontractor)) {
            return $this->sendError('Subcontractor not found');
        }

        return $this->sendResponse(SubcontractorResource::make($subcontractor), 'Subcontractor retrieved successfully');
    }

    /**
     * Update the specified Subcontractor in storage.
     * PUT/PATCH /subcontractors/{id}
     */
    public function update($id, UpdateSubcontractorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Subcontractor $subcontractor */
        $subcontractor = $this->subcontractorRepository->find($id);

        if (empty($subcontractor)) {
            return $this->sendError('Subcontractor not found');
        }

        $subcontractor = $this->subcontractorRepository->update($input, $id);

        return $this->sendResponse(SubcontractorResource::make($subcontractor), 'Subcontractor updated successfully');
    }

    public function updateTasks($id, Request $request)
    {
        $this->authorize('supplier_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Supplier $subcontractor */
        $subcontractor = $this->subcontractorRepository->find($id);

        if (empty($subcontractor)) {
            return $this->sendError('Subcontractor not found');
        }

        if (isset($request->tasks)) {
            $oriList = $subcontractor->tasks->pluck('task_id')->toArray();
            foreach ($request->tasks as $task) {
                if (isset($task['task_id'])) {
                    // update
                    $data = SubcontractorHasTask::where('task_id', $task['task_id'])->where('subcontractor_id', $id)->first();
                    $data->update([
                        'outline_agreement_id' => $task['outline_agreement_id'] ?? null,
                        'task_name'            => $task['task_name'],
                        'task_no'              => $task['task_no'],
                        'qty'                  => $task['qty'],
                        'unit'                 => $task['unit'],
                        'unit_price'           => $task['unit_price'],
                        'total_price'          => $task['qty'] * $task['unit_price'],
                        // 'remark' => $task['remark'],
                    ]);
                    // remove task ID from $oriList
                    $index = array_search($task['task_id'], $oriList);
                    if ($index !== false) {
                        unset($oriList[$index]);
                    }

                } else {
                    // create
                    $newTask = Task::create([
                        'group_task_id' => $task['group_task_id'] ?? null,
                        'name'          => $task['task_name'],
                        'qty'           => $task['qty'],
                        'task_no'       => $task['task_no'],
                        'unit'          => $task['unit'],
                        'unit_price'    => $task['unit_price'],
                        'total_price'   => $task['qty'] * $task['unit_price'],
                    ]);
                    SubcontractorHasTask::create([
                        'subcontractor_id'     => $id,
                        'outline_agreement_id' => $task['outline_agreement_id'] ?? null,
                        'group_task_id'        => $task['group_task_id'] ?? null,
                        'group_task_name'      => $task['group_task_name'] ?? null,
                        'task_id'              => $newTask->id,
                        'task_name'            => $task['task_name'],
                        'task_no'              => $task['task_no'],
                        'qty'                  => $task['qty'],
                        'unit'                 => $task['unit'],
                        'unit_price'           => $task['unit_price'],
                        'total_price'          => $task['qty'] * $task['unit_price'],
                        // 'remark'               => $task['remark'],
                        'status'               => SubcontractorHasTask::ACTIVE,
                    ]);
                }

            }
            // remaining from oriList are to be deleted
            foreach ($oriList as $deleteId) {
                SubcontractorHasTask::where('subcontractor_id', $id)->where('task_id', $deleteId)->forceDelete();
            }
        }

        return $this->sendResponse(SubcontractorResource::make($subcontractor), 'Subcontractor tasks updated successfully');

    }

    /**
     * Remove the specified Subcontractor from storage.
     * DELETE /subcontractors/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var Subcontractor $subcontractor */
        $subcontractor = $this->subcontractorRepository->find($id);

        if (empty($subcontractor)) {
            return $this->sendError('Subcontractor not found');
        }

        $subcontractor->delete();

        return $this->sendSuccess('Subcontractor deleted successfully');
    }

    public function addTasks($subcontractorId, Request $request)
    {
        $this->authorize('supplier_update');

        $input = $request->all();

        foreach ($input['tasks'] as $task) {
            SubcontractorHasTask::create([
                'subcontractor_id'     => $subcontractorId,
                'outline_agreement_id' => $input['OASearchId'],
                'group_task_id'        => $input['groupTaskSearchId'],
                'group_task_name'      => $input['groupTaskName'],
                'task_id'              => $task['id'],
                'task_name'            => $task['name'],
                'task_no'              => $task['task_no'],
                'qty'                  => $task['qty'],
                'unit'                 => $task['unit'],
                'unit_price'           => $task['unit_price'],
                'total_price'          => $task['total_price'],
                // 'remark'               => $task['remark'],
                'status'               => SubcontractorHasTask::ACTIVE,
            ]);
            SubcontractorTaskPrice::where('subcontractor_id', $subcontractorId)->where('task_id', $task['id'])->where('unit_price', 0)->update([
                'unit_price' => $task['unit_price'],
            ]);
        }

        $subcontractor = Subcontractor::find($subcontractorId);

        return $this->sendSuccess(SubcontractorResource::make($subcontractor));

    }
}