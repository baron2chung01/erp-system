<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateAttendanceAPIRequest;
use App\Http\Resources\Mobile\AttendanceResource;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Traits\Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;

/**
 * Class AttendanceController
 * @package App\Http\Controllers\API
 */
class AttendanceAPIController extends AppBaseController
{
    /** @var  AttendanceRepository */
    private $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepo)
    {
        $this->attendanceRepository = $attendanceRepo;
    }

    /**
     * Display a listing of the Attendance.
     * GET|HEAD /attendances
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Attendance::where('status', Attendance::ACTIVE);

        if ($request->date_from != null) {
            $query = $query->where('attendance_at', '>=', $request->date_from);
        }

        if ($request->date_to != null) {
            $query = $query->where('attendance_at', '<=', date_add(date_create($request->date_to), date_interval_create_from_date_string("1 day - 1 second")));
        }

        $attendances = $query->get();

        return $this->sendResponse(AttendanceResource::collection($attendances), 'Attendances retrieved successfully');
    }

    /**
     * Store a newly created Attendance in storage.
     * POST /attendances
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $user      = auth('employees')->user();
        $workOrder = $user->workOrders->where('id', $input['work_order_id'])->first();
        if (!isset($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        // if ($workOrder->location->id != $input['location_id']) {
        //     return $this->sendError('Location not found');
        // }

        $validator = $this->attendanceRepository->createValidation($input);
        $inRange   = $this->attendanceRepository->checkLatLonInRange($input);

        $input['in_range'] = $inRange;
        if (!isset($input['attendance_at'])) {
            $input['attendance_at'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        if ($validator->fails()) {
            return $this->sendApiError($validator->errors(), 500);
        }

        $attendance = $this->attendanceRepository->create($input);

        if ($inRange) {
            return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance saved successfully');
        }

        return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance saved successfully But it\'s not in range');
    }

    /**
     * Display the specified Attendance.
     * GET|HEAD /attendances/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Attendance $attendance */
        $attendance = $this->attendanceRepository->find($id);

        if (empty($attendance)) {
            return $this->sendError('Attendance not found');
        }

        return $this->sendResponse(AttendanceResource::make($attendance), 'Attendance retrieved successfully');
    }
}
