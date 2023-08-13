<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateOutlineAgreementAPIRequest;
use App\Http\Requests\API\UpdateOutlineAgreementAPIRequest;
use App\Http\Resources\OutlineAgreementResource;
use App\Models\Client;
use App\Models\GroupTask;
use App\Models\Location;
use App\Models\OutlineAgreement;
use App\Models\OutlineAgreementHasClient;
use App\Models\OutlineAgreementHasGroupTask;
use App\Models\OutlineAgreementHasLocation;
use App\Models\PurchaseOrderHasSubcontractorTask;
use App\Models\PurchaseOrderHasTask;
use App\Models\Task;
use App\Repositories\OutlineAgreementRepository;
use App\Traits\MapTask;
use App\Traits\MapTaskNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;

/**
 * Class OutlineAgreementController
 * @package App\Http\Controllers\API
 */
class OutlineAgreementAPIController extends AppBaseController
{
    /** @var  OutlineAgreementRepository */
    private $outlineAgreementRepository;

    public function __construct(OutlineAgreementRepository $outlineAgreementRepo)
    {
        $this->outlineAgreementRepository = $outlineAgreementRepo;
    }

    /**
     * Display a listing of the OutlineAgreement.
     * GET|HEAD /outlineAgreements
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('outline_agreement_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->outlineAgreementRepository->all([
                'status' => OutlineAgreement::ACTIVE,
            ])->pluck('display_name', 'id'));
        }

        if (isset($input['search'])) {
            $query = OutlineAgreement::where('oa_number', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('name', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $outlineAgreements = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $outlineAgreements = $this->outlineAgreementRepository->all(
                $input,
                $current,
                $pageSize,
                ['oaHasGroupTasks', 'clients']
            );

            $total = count($this->outlineAgreementRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => OutlineAgreementResource::collection($outlineAgreements),
            'total' => $total,
        ], 'Outline Agreements retrieved successfully');
    }

    /**
     * Store a newly created OutlineAgreement in storage.
     * POST /outlineAgreements
     *
     * @param CreateOutlineAgreementAPIRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('outline_agreement_create');

        $validated = $request->validate([
            'locations_id' => 'required',
            'clients_id'   => 'required',
            'oa_number'    => 'nullable|unique:outline_agreements',
        ]);

        $input = $request->all();

        $outlineAgreement = $this->outlineAgreementRepository->create($input);

        if (isset($request->clients_id)) {
            foreach ($input['clients_id'] as $clientId) {
                $client = Client::find($clientId);
                OutlineAgreementHasClient::create([
                    'outline_agreement_id' => $outlineAgreement->id,
                    'client_id'            => $clientId,
                    'status'               => OutlineAgreementHasClient::ACTIVE,
                ]);
            }
        }

        if (isset($request->locations_id)) {
            foreach ($input['locations_id'] as $locationId) {
                $location = Location::find($locationId);
                OutlineAgreementHasLocation::create([
                    'outline_agreement_id' => $outlineAgreement->id,
                    'location_id'          => $locationId,
                ]);
            }
        }

        $outlineAgreement = OutlineAgreement::with(['oaHasGroupTasks', 'oaHasGroupTasks.tasks'])->find($outlineAgreement->id);

        return $this->sendResponse(OutlineAgreementResource::make($outlineAgreement), 'Outline Agreement saved successfully');
    }

    /**
     * Display the specified OutlineAgreement.
     * GET|HEAD /outlineAgreements/{id}
     *
     * @param int $id
     *
     * @return Response
     */public function show($id)
    {
        $this->authorize('outline_agreement_view');

        /** @var OutlineAgreement $outlineAgreement */
        $outlineAgreement = $this->outlineAgreementRepository->find($id, ['clients', 'oaHasGroupTasks.tasks']);

        if (empty($outlineAgreement)) {
            return $this->sendError('Outline Agreement not found');
        }

        return $this->sendResponse(OutlineAgreementResource::make($outlineAgreement), 'Outline Agreement retrieved successfully');
    }

    /**
     * Update the specified OutlineAgreement in storage.
     * PUT/PATCH /outlineAgreements/{id}
     *
     * @param int $id
     * @param UpdateOutlineAgreementAPIRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('outline_agreement_update');

        $validated = $request->validate([
            'oa_number' => 'nullable|unique:outline_agreements,oa_number,' . $id,
        ]);

        $input = $request->all();

        /** @var OutlineAgreement $outlineAgreement */
        $outlineAgreement = $this->outlineAgreementRepository->find($id);

        if (empty($outlineAgreement)) {
            return $this->sendError('Outline Agreement not found');
        }

        // handle clients
        $outlineAgreement = $this->outlineAgreementRepository->update($input, $id);

        if (isset($request->clients_id)) {
            $oriClient = $outlineAgreement->clients->pluck('id')->toArray();
            $deleteClient = array_diff($oriClient, $input['clients_id']);
            $createClient = array_diff($input['clients_id'], $oriClient);

            foreach ($deleteClient as $clientId) {
                OutlineAgreementHasClient::where('outline_agreement_id', $id)->where('client_id', $clientId)->forceDelete();
            }
            foreach ($createClient as $clientId) {
                OutlineAgreementHasClient::create(
                    [
                        'outline_agreement_id' => $id,
                        'client_id'            => $clientId,
                        'status'               => OutlineAgreementHasClient::ACTIVE,
                    ]
                );
            }
        }

        if (isset($request->locations_id)) {
            $oriLocation = $outlineAgreement->locations->pluck('id')->toArray() ?? [];
            $deleteLocation = array_diff($oriLocation, $input['locations_id']);
            $createLocation = array_diff($input['locations_id'], $oriLocation);

            foreach ($deleteLocation as $locationId) {
                OutlineAgreementHasLocation::where('outline_agreement_id', $id)->where('location_id', $locationId)->forceDelete();
            }
            foreach ($createLocation as $locationId) {
                OutlineAgreementHasLocation::create(
                    [
                        'outline_agreement_id' => $id,
                        'location_id'          => $locationId,
                    ]
                );
            }
        }

        // handle group tasks
        $tmp = [];
        $outlineAgreement = $this->outlineAgreementRepository->update($input, $id);
        // Map outline agreement with group tasks and tasks
        if (isset($input['group_tasks'])) {
            foreach ($input['group_tasks'] as $inputGroupT) {
                $tmp[$inputGroupT['name']] = array_merge(['group_task_id' => $inputGroupT['id']], ['tasks' => $inputGroupT['tasks']]);
            }
        }

        MapTaskNew::mapTasks('outline_agreement_id', $outlineAgreement->id, $tmp, 'App\Models\OutlineAgreementHasGroupTask');

        // sync the tasks down levels
        OutlineAgreementAPIController::syncPivot($outlineAgreement);

        return $this->sendResponse(OutlineAgreementResource::make($outlineAgreement), 'OutlineAgreement updated successfully');
    }

    /**
     * Remove the specified OutlineAgreement from storage.
     * DELETE /outlineAgreements/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('outline_agreement_delete');

        /** @var OutlineAgreement $outlineAgreement */
        $outlineAgreement = $this->outlineAgreementRepository->find($id);

        if (empty($outlineAgreement)) {
            return $this->sendError('Outline Agreement not found');
        }

        $outlineAgreement->delete();

        return $this->sendSuccess('Outline Agreement deleted successfully');
    }

    public static function syncPivot($oa)
    {
        $purchaseOrders = $oa->purchaseOrders;

        foreach ($purchaseOrders as $po) {
            if (isset($po->poHasGroupTasks)) {
                foreach ($po->poHasGroupTasks as $groupTask) {
                    if (isset($groupTask->tasks)) {
                        foreach ($groupTask->tasks as $task) {
                            PurchaseOrderHasTask::firstOrCreate([
                                'purchase_order_id' => $po->id,
                                'group_task_id'     => $groupTask->id,
                                'task_id'           => $task->id,
                            ], [
                                'group_task_name' => $groupTask->group_task_name,
                                'task_name'       => $task->name,
                                'task_no'         => $task->task_no,
                                'qty'             => $task->qty,
                                'unit'            => $task->unit,
                                'unit_price'      => $task->unit_price,
                                'total_price'     => $task->total_price,
                            ]);
                            PurchaseOrderHasSubcontractorTask::firstOrCreate([
                                'purchase_order_id' => $po->id,
                                'group_task_id'     => $groupTask->id,
                                'task_id'           => $task->id,
                            ], [
                                'group_task_name' => $groupTask->group_task_name,
                                'task_name'       => $task->name,
                                'task_no'         => $task->task_no,
                                'qty'             => $task->qty,
                                'unit'            => $task->unit,
                                'unit_price'      => $task->unit_price,
                                'total_price'     => $task->total_price,
                            ]);
                        }
                    }
                }
            }
            // sync total_price to PO
            $po->update([
                'total_price' => $po->taskInfo()->sum('total_price'),
            ]);

        }

    }
}
