<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\Mobile\WorkOrderResource;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\WorkOrder;
use App\Models\WorkOrderEmployee;
use App\Repositories\WorkOrderRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Response;

/**
 * Class WorkOrderController
 * @package App\Http\Controllers\API
 */
class WorkOrderAPIController extends AppBaseController
{
    /** @var  WorkOrderRepository */
    private $workOrderRepository;

    public function __construct(WorkOrderRepository $workOrderRepo)
    {
        $this->workOrderRepository = $workOrderRepo;
    }

    /**
     * Display a listing of the WorkOrder.
     * GET|HEAD /workOrders
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = auth('employees')->user();

        if (in_array(Employee::LEADER, $user->roles->pluck('id')->toArray())) {

            $query = $user->workOrders();

        } else {

            $query = $user->workOrders();

        }

        if ($request->date_from != null) {
            $query = $query->where('ended_at', '>=', $request->date_from);
        }

        if ($request->date_to != null) {
            $query = $query->where('started_at', '<=', date_add(date_create($request->date_to), date_interval_create_from_date_string("1 day - 1 second")));
        }

        if ($request->status != null) {
            $query = $query->where('work_orders.status', WorkOrder::MOBILE_STATUS[$request->status]);
        }

        $workOrders = $query->get();

        return $this->sendResponse(WorkOrderResource::collection($workOrders), 'Work Orders retrieved successfully');
    }

    /**
     * Display the specified WorkOrder.
     * GET|HEAD /workOrders/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var WorkOrder $workOrder */
        $user = auth('employees')->user();

        if (in_array(Employee::LEADER, $user->roles->pluck('id')->toArray())) {

            $workOrder = WorkOrder::where('work_orders.status', '<', WorkOrder::COMPLETED)->where('started_at', '<=', now()->addDays(7))->where('id', $id)->first();

        } else {

            $workOrder = $user->workOrders->where('id', $id)->first();

        }

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        return $this->sendResponse(WorkOrderResource::make($workOrder), 'Work Order retrieved successfully');
    }

    /**
     * Update the specified WorkOrder in storage.
     * PUT/PATCH /workOrders/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        if ($request->status == WorkOrder::COMPLETED) {
            if (!count($workOrder->resultImages)) {
                return $this->sendError('Update status fail. Reason: Result images could not be found.');
            }
            if (!count($workOrder->signatureImages)) {
                return $this->sendError('Update status fail. Reason: Signature images could not be found.');
            }
        }

        $input = $request->all();

        $workOrder->update($input);

        $workOrder->refresh();

        return $this->sendResponse(WorkOrderResource::make($workOrder), 'WorkOrder updated successfully');
    }

    public function updateResultImage($id, Request $request)
    {
        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        $input = $request->all();

        $images = $input['images'] ?? [];
        $imageCount = Asset::where('related_type', $this->workOrderRepository->model())
            ->where('related_id', $workOrder->id)
            ->where('asset_type', WorkOrder::RESULT_IMAGE)
            ->count();
        if ($imageCount >= 20 || ($imageCount + count($images) > 20)) {
            return $this->sendError('完工相不能超過20張, 現有張數: ' . $imageCount, 400);
        }

        if (isset($input['images'])) {
            foreach ($input['images'] as $image) {
                $imageName = Str::random(6) . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('work-order-result-image'), $imageName);
                Asset::create([
                    'related_type'  => $this->workOrderRepository->model(),
                    'related_id'    => $workOrder->id,
                    'asset_type'    => WorkOrder::RESULT_IMAGE,
                    'url'           => '/work-order-result-image/' . $imageName,
                    'resource_path' => public_path('work-order-result-image') . '/' . $imageName,
                    'file_size'     => $image->getSize(),
                    'file_name'     => $imageName,
                    'status'        => Asset::ACTIVE,
                ]);
            }
        }

        // auto update PO status

        $POHasSign = $workOrder->purchaseOrder->has('workOrders.signatureImages')->exists();

        $POHasResultImage = $workOrder->purchaseOrder->has('workOrders.resultImages')->exists();

        $POstatus = null;

        if ($POHasSign) {
            if ($POHasResultImage) {
                $POstatus = 6;
            } else {
                $POstatus = 4;
            }
        } else {
            if ($POHasResultImage) {
                $POstatus = 5;
            }
        }

        if (isset($POstatus)) {
            $workOrder->purchaseOrder->update([
                'status' => $POstatus,
            ]);
        }

        // auto update work order status
        // if (count($workOrder->resultImages) && count($workOrder->signatureImages)) {
        //     $workOrder->update([
        //         'status' => WorkOrder::COMPLETED,
        //     ]);
        // }

        // $workOrder->refresh();

        return $this->sendResponse(WorkOrderResource::make($workOrder), 'WorkOrder updated successfully');
    }

    public function updateSignature($id, Request $request)
    {
        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        $input = $request->all();

        if (isset($input['image'])) {
            $image = $request->image; // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(6) . time() . '.png';
            if (!is_dir(public_path('work-order-signature-image'))) {
                mkdir(public_path('work-order-signature-image'));
            }
            \File::put(public_path('work-order-signature-image') . '/' . $imageName, base64_decode($image));

            Asset::create([
                'related_id'    => $workOrder->id,
                'asset_type'    => WorkOrder::SIGNATURE_IMAGE,
                'created_by'    => auth('employees')->user()->id,
                'related_type'  => $this->workOrderRepository->model(),
                'url'           => '/work-order-signature-image/' . $imageName,
                'resource_path' => public_path('work-order-signature-image') . '/' . $imageName,
                'file_size'     => 0,
                'file_name'     => $imageName,
                'status'        => Asset::ACTIVE,
            ]);
        }

        $workOrder->refresh();

        // auto update PO status

        $POHasSign = $workOrder->purchaseOrder->has('workOrders.signatureImages')->exists();

        $POHasResultImage = $workOrder->purchaseOrder->has('workOrders.resultImages')->exists();

        $POstatus = null;

        if ($POHasSign) {
            if ($POHasResultImage) {
                $POstatus = 6;
            } else {
                $POstatus = 4;
            }
        } else {
            if ($POHasResultImage) {
                $POstatus = 5;
            }
        }

        if (isset($POstatus)) {
            $workOrder->purchaseOrder->update([
                'status' => $POstatus,
            ]);
        }

        // auto update work order status
        // if (count($workOrder->resultImages) && count($workOrder->signatureImages)) {
        //     $workOrder->update([
        //         'status' => WorkOrder::COMPLETED,
        //     ]);
        // }

        return $this->sendResponse(WorkOrderResource::make($workOrder), 'WorkOrder updated successfully');
    }
}
